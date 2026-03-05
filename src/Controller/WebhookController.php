<?php

namespace App\Controller;

use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebhookController extends AbstractController
{
    /**
     * Stripe webhook endpoint — must be excluded from CSRF.
     * Add this URL to your Stripe Dashboard webhook settings.
     */
    #[Route('/webhook/stripe', name: 'webhook_stripe', methods: ['POST'])]
    public function stripe(Request $request, PaymentService $paymentService): Response
    {
        $success = $paymentService->handleWebhook($request);

        if (!$success) {
            return new Response('Webhook signature verification failed.', 400);
        }

        return new Response('OK', 200);
    }
}
