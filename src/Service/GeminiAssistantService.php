<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GeminiAssistantService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ParameterBagInterface $params
    ) {
    }

    public function generateSkinDetails(string $skinName, ?string $gameName): array
    {
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? $this->params->get('gemini_api_key') ?? '';
        
        if (empty($apiKey)) {
            throw new \Exception('Gemini API key is not configured.');
        }

        $prompt = "You are an expert gaming e-commerce assistant. We need to create an amazing description, suggest a rarity, identify the game, and suggest a valid square image URL for a game skin.
Skin Name: {$skinName}
" . ($gameName ? "Game (if known): {$gameName}\n" : "") . "

Respond ONLY in JSON format with the following structure:
{
    \"description\": \"A short, epic, and engaging 2-3 sentence description of the skin for a shop interface in French.\",
    \"rarity\": \"One of: COMMON, UNCOMMON, RARE, EPIC, LEGENDARY\",
    \"game\": \"The exact name of the game this skin belongs to (e.g., 'CS2', 'Valorant', 'League of Legends')\",
    \"imageUrl\": \"A valid realistic looking placeholder image URL for this type of skin (e.g. from an image placeholder service or wiki if known)\"
}";

        $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=' . trim($apiKey), [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]
        ]);

        $data = $response->toArray();

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
             throw new \Exception('Invalid response from Gemini API.');
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'];
        
        // Gemini often wraps JSON in markdown block
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        
        // Extract the parsed Gemini data
        $result = json_decode(trim($text), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             throw new \Exception('Failed to parse JSON response from Gemini.');
        }

        // --- SECOND CALL: Nano Banana API for robust image generation ---
        $imageUrl = $result['imageUrl'] ?? 'https://placehold.co/400x400/1E1E1E/e60013?text=' . urlencode('Skin Placeholder');
        
        // Removed broken image generation API call since gemini-2.5-flash-image is not available
        // on standard API keys and Imagen 3 throws 404/403 for predict method.
        // We now rely entirely on the placeholder URL returned by the text model
        // or a default placeholder.

        return [
            'description' => $result['description'] ?? '',
            'rarity' => $result['rarity'] ?? 'COMMON',
            'game' => $result['game'] ?? '',
            'imageUrl' => $imageUrl
        ];
    }
}
