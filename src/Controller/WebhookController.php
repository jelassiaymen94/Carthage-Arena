<?php

namespace App\Controller;

use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebhookController extends AbstractController
{
    #[Route('/webhook/stripe', name: 'webhook_stripe', methods: ['POST'])]
    public function stripe(Request $request, PaymentService $paymentService): Response
    {
        try {
            $paymentService->handleWebhook($request);
            return new Response('OK', 200);
        } catch (\Exception $e) {
            return new Response('Error', 400);
        }
    }
}
