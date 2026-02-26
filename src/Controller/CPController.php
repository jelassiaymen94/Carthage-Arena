<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/recharger')]
class CPController extends AbstractController
{
    private array $cpPackages = [
        'starter' => ['amount' => 500, 'price' => 4.99, 'label' => '500 CP'],
        'classic' => ['amount' => 1000, 'price' => 9.99, 'label' => '1000 CP (Bonus +50)'],
        'premium' => ['amount' => 2500, 'price' => 24.99, 'label' => '2500 CP (Bonus +250)'],
        'ultra' => ['amount' => 5000, 'price' => 49.99, 'label' => '5000 CP (Bonus +500)'],
    ];

    #[Route('', name: 'app_cp_shop')]
    public function index(): Response
    {
        return $this->render('cp/shop.html.twig', [
            'packages' => $this->cpPackages,
        ]);
    }

    #[Route('/checkout/{package}', name: 'app_cp_checkout', methods: ['POST'])]
    public function checkout(string $package, Request $request): Response
    {
        if (!isset($this->cpPackages[$package])) {
            throw $this->createNotFoundException('Package not found');
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $packageData = $this->cpPackages[$package];

        $clientSecret = null;
        $stripeAvailable = class_exists(StripeClient::class);
        if ($stripeAvailable) {
            $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? getenv('STRIPE_SECRET_KEY');
            if ($stripeSecret && $stripeSecret !== 'sk_test_YOUR_SECRET_KEY') {
                $stripe = new StripeClient($stripeSecret);
                $intent = $stripe->paymentIntents->create([
                    'amount' => (int) round($packageData['price'] * 100),
                    'currency' => 'usd',
                    'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
                    'metadata' => [
                        'user_id' => $user->getId(),
                        'cp_amount' => $packageData['amount'],
                        'package_type' => $package,
                    ],
                ]);

                $clientSecret = $intent->client_secret ?? null;
            }
        }

        $publishable = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? getenv('STRIPE_PUBLISHABLE_KEY');
        $webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? getenv('STRIPE_WEBHOOK_SECRET');
        $isWebhookSecretValid = !empty($webhookSecret) && $webhookSecret !== 'whsec_YOUR_WEBHOOK_SECRET';

        return $this->render('cp/checkout.html.twig', [
            'package' => $package,
            'packageData' => $packageData,
            'user' => $user,
            'client_secret' => $clientSecret,
            'stripe_available' => $stripeAvailable,
            'stripe_publishable' => ($publishable !== 'pk_test_YOUR_PUBLISHABLE_KEY' ? $publishable : ''),
            'webhook_secret_set' => $isWebhookSecretValid,
        ]);
    }

    #[Route('/payment-success', name: 'app_cp_payment_success', methods: ['POST'])]
    public function paymentSuccess(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $cpAmount = isset($data['cp_amount']) ? (int) $data['cp_amount'] : 0;
        $paymentIntentId = $data['payment_intent_id'] ?? null;

        if (!$cpAmount || !$paymentIntentId) {
            return $this->json(['error' => 'Invalid data'], 400);
        }

        // Verify the payment intent with Stripe to ensure it actually succeeded
        $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? getenv('STRIPE_SECRET_KEY');
        if (!$stripeSecret || $stripeSecret === 'sk_test_YOUR_SECRET_KEY') {
            return $this->json(['error' => 'Stripe not configured'], 500);
        }

        try {
            $stripe = new StripeClient($stripeSecret);
            $intent = $stripe->paymentIntents->retrieve($paymentIntentId);

            if ($intent->status !== 'succeeded') {
                return $this->json(['error' => 'Payment not completed'], 400);
            }

            // Verify this intent belongs to this user
            $intentUserId = $intent->metadata['user_id'] ?? null;
            if ((string) $intentUserId !== (string) $user->getId()) {
                return $this->json(['error' => 'Unauthorized'], 403);
            }

            $user->setBalance($user->getBalance() + $cpAmount);
            $em->flush();

            return $this->json(['success' => true, 'new_balance' => $user->getBalance()]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Stripe error: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/webhook/payment-success', name: 'app_cp_webhook_success', methods: ['POST'])]
    public function webhookSuccess(Request $request, EntityManagerInterface $em): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        $webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? getenv('STRIPE_WEBHOOK_SECRET');
        $isWebhookSecretValid = !empty($webhookSecret) && $webhookSecret !== 'whsec_YOUR_WEBHOOK_SECRET';
        $event = null;

        if ($isWebhookSecretValid && class_exists(Webhook::class)) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } catch (\UnexpectedValueException $e) {
                return $this->json(['error' => 'Invalid payload'], 400);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                return $this->json(['error' => 'Invalid signature'], 400);
            }
        } else {
            $event = json_decode($payload, true);
        }

        $type = is_array($event) ? ($event['type'] ?? null) : ($event->type ?? null);
        $object = is_array($event) ? ($event['data']['object'] ?? null) : ($event->data->object ?? null);

        if ($type === 'payment_intent.succeeded' || $type === 'charge.succeeded') {
            $metadata = is_array($object) ? ($object['metadata'] ?? []) : ($object->metadata ?? []);
            $userId = $metadata['user_id'] ?? null;
            $cpAmount = isset($metadata['cp_amount']) ? (int)$metadata['cp_amount'] : 0;

            error_log("Webhook success: type=$type, userId=$userId, cpAmount=$cpAmount");

            if ($userId) {
                $user = $em->getRepository(User::class)->find($userId);
                if ($user) {
                    $user->setBalance($user->getBalance() + $cpAmount);
                    $em->flush();
                }
            }
        }

        return $this->json(['success' => true]);
    }
}
