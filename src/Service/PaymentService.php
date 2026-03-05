<?php

namespace App\Service;

use App\Entity\Skin;
use App\Entity\Merch;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentService
{
    private StripeClient $stripe;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PurchaseService $purchaseService,
        private UrlGeneratorInterface $urlGenerator,
        private string $stripeSecretKey,
        private string $stripeWebhookSecret,
    ) {
        $this->stripe = new StripeClient($this->stripeSecretKey);
    }

    /**
     * Creates a Stripe Checkout session for any item (skin or merch).
     */
    public function createCheckoutSession(
        Skin|Merch $item,
        User $user,
        string $itemType
    ): string {
        $itemName = $item->getName();
        $priceInCents = (int) round($item->getPrice() * 100); // Assume price is in EUR

        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $user->getEmail(),
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $priceInCents,
                    'product_data' => [
                        'name' => $itemName,
                        'description' => method_exists($item, 'getDescription') ? ($item->getDescription() ?? '') : '',
                        'images' => $item->getImageUrl() ? [$item->getImageUrl()] : [],
                    ],
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'item_id'   => (string) $item->getId(),
                'item_type' => $itemType, // 'skin' or 'merch'
                'user_id'   => (string) $user->getId(),
            ],
            'success_url' => $this->urlGenerator->generate(
                'app_shop_stripe_success',
                ['session_id' => '{CHECKOUT_SESSION_ID}'],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'cancel_url' => $this->urlGenerator->generate(
                'app_shop_stripe_cancel',
                ['id' => $item->getId(), 'type' => $itemType],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $session->url;
    }

    /**
     * Handles incoming Stripe webhooks. Verifies signature and processes events.
     * Returns true on success, false on failure.
     */
    public function handleWebhook(Request $request): bool
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature', '');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->stripeWebhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return false;
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            return false;
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $this->fulfillOrder($session);
        }

        return true;
    }

    /**
     * Fulfills an order after successful Stripe payment.
     */
    private function fulfillOrder(\Stripe\Checkout\Session $session): void
    {
        $metadata = $session->metadata;
        $itemId   = $metadata->item_id ?? null;
        $itemType = $metadata->item_type ?? null;
        $userId   = $metadata->user_id ?? null;

        if (!$itemId || !$itemType || !$userId) {
            return;
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return;
        }

        $this->purchaseService->fulfillStripeOrder($itemId, $itemType, $user);
    }
}
