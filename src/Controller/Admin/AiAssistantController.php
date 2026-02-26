<?php

namespace App\Controller\Admin;

use App\Service\GeminiAssistantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/api')]
class AiAssistantController extends AbstractController
{
    #[Route('/generate-skin', name: 'admin_api_generate_skin', methods: ['POST'])]
    public function generateSkin(Request $request, GeminiAssistantService $gemini): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $skinName = $data['name'] ?? null;
        $gameName = $data['game'] ?? null;

        if (!$skinName) {
            return $this->json(['error' => 'Skin name is required'], 400);
        }

        try {
            $result = $gemini->generateSkinDetails($skinName, $gameName);
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
