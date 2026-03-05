<?php

namespace App\Service;

use App\Entity\Skin;
use App\Entity\User;
use App\Enum\SkinType;
use App\Message\DeliverSkinMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaymentService
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {}

    public function createPaymentIntent(Skin $skin, User $user): array
    {
        // Simuler création d'intent de paiement Stripe
        // $stripe = new \Stripe\StripeClient($this->stripeSecretKey);
        // $intent = $stripe->paymentIntents->create([
        //     'amount' => $skin->getPrice() * 100,
        //     'currency' => 'usd',
        //     'metadata' => ['skin_id' => $skin->getId(), 'user_id' => $user->getId()],
        // ]);

        return [
            'client_secret' => 'pi_fake_secret_' . uniqid(),
            'id' => 'pi_' . uniqid(),
        ];
    }

    public function handleWebhook(Request $request): void
    {
        // Vérifier la signature Stripe
        // $event = \Stripe\Webhook::constructEvent($request->getContent(), $request->headers->get('stripe-signature'), $this->webhookSecret);

        // Simuler traitement
        $payload = json_decode($request->getContent(), true);
        if ($payload['type'] === 'payment_intent.succeeded') {
            $this->processSuccessfulPayment($payload['data']['object']);
        }
    }

    private function processSuccessfulPayment(array $paymentIntent): void
    {
        $skinId = $paymentIntent['metadata']['skin_id'];
        $userId = $paymentIntent['metadata']['user_id'];

        // Envoyer message pour livraison asynchrone
        $this->messageBus->dispatch(new DeliverSkinMessage($skinId, $userId));
    }


}