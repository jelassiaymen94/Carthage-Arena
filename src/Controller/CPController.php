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

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $packageData = $this->cpPackages[$package];

        // Create a Stripe PaymentIntent and pass the client_secret to the template
        $clientSecret = null;
        $stripeAvailable = class_exists(StripeClient::class);
        if ($stripeAvailable) {
            $stripeSecret = $_ENV['STRIPE_SECRET'] ?? getenv('STRIPE_SECRET');
            if ($stripeSecret) {
                $stripe = new StripeClient($stripeSecret);
                $intent = $stripe->paymentIntents->create([
                    'amount' => (int) round($packageData['price'] * 100),
                    'currency' => 'usd',
                    'metadata' => [
                        'user_id' => $user->getId(),
                        'cp_amount' => $packageData['amount'],
                        'package_type' => $package,
                    ],
                ]);

                $clientSecret = $intent->client_secret ?? null;
            }
        }

        $publishable = $_ENV['STRIPE_PUBLISHABLE'] ?? getenv('STRIPE_PUBLISHABLE');
        $webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? getenv('STRIPE_WEBHOOK_SECRET');
        $isWebhookSecretValid = !empty($webhookSecret) && $webhookSecret !== 'whsec_YOUR_WEBHOOK_SECRET';

        return $this->render('cp/checkout.html.twig', [
            'package' => $package,
            'packageData' => $packageData,
            'user' => $user,
            'client_secret' => $clientSecret,
            'stripe_available' => $stripeAvailable,
            'stripe_publishable' => $publishable,
            'webhook_secret_set' => $isWebhookSecretValid,
        ]);
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
            // Fallback for development without webhook secret
            $event = json_decode($payload, true);
        }

        // Normalize event object access
        $type = is_array($event) ? ($event['type'] ?? null) : ($event->type ?? null);
        $object = is_array($event) ? ($event['data']['object'] ?? null) : ($event->data->object ?? null);

        if ($type === 'payment_intent.succeeded' || $type === 'charge.succeeded') {
            $metadata = is_array($object) ? ($object['metadata'] ?? []) : ($object->metadata ?? []);
            $userId = $metadata['user_id'] ?? null;
            $cpAmount = isset($metadata['cp_amount']) ? (int)$metadata['cp_amount'] : 0;

            error_log("Webhook success: type=$type, userId=$userId, cpAmount=$cpAmount");

            if ($userId) {
                $user = $em->getRepository(User::class)->find($userId);
                error_log("Webhook user found: " . ($user ? 'yes' : 'no') . " with balance " . ($user ? $user->getBalance() : 0));
                if ($user) {
                    $user->setBalance($user->getBalance() + $cpAmount);
                    $em->flush();
                    error_log("Webhook user new balance: " . $user->getBalance());
                }
            } else {
                error_log("Webhook no user id found in metadata: " . json_encode($metadata));
            }
        } else {
            error_log("Webhook received unsupported type: $type");
        }

        return $this->json(['success' => true]);
    }
}