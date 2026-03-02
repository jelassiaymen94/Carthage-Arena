<?php

namespace App\Controller;

use App\Service\GrokAiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AiChatController extends AbstractController
{
    #[Route('/ai/chat', name: 'app_ai_chat', methods: ['POST'])]
    public function chat(Request $request, GrokAiService $grokService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';
        $history = $data['history'] ?? [];

        if (empty($message)) {
            return new JsonResponse(['error' => 'Pas de message.'], 400);
        }

        $messages = array_merge($history, [['role' => 'user', 'content' => $message]]);

        $reply = $grokService->chat($messages);

        return new JsonResponse(['reply' => $reply]);
    }
}
