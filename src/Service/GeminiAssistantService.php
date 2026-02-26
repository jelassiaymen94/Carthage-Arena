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
        $imageUrl = $result['imageUrl'] ?? '';
        try {
            // We use the same API key for the image endpoint
            $imagePrompt = "Generate a realistic, high-quality, square shop thumbnail for the game skin: {$skinName}";
            $imageResponse = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent?key=' . trim($apiKey), [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [['text' => $imagePrompt]]
                        ]
                    ]
                ]
            ]);

            $imageData = $imageResponse->toArray(false); // don't throw exception to allow fallback
            
            // Extract generation result (usually base64 from inlineData)
            if (isset($imageData['candidates'][0]['content']['parts'][0]['inlineData'])) {
                $inline = $imageData['candidates'][0]['content']['parts'][0]['inlineData'];
                $imageUrl = 'data:' . ($inline['mimeType'] ?? 'image/jpeg') . ';base64,' . $inline['data'];
            } elseif (isset($imageData['predictions'][0]['url'])) {
                $imageUrl = $imageData['predictions'][0]['url'];
            } elseif (isset($imageData['candidates'][0]['content']['parts'][0]['text']) && strpos($imageData['candidates'][0]['content']['parts'][0]['text'], 'http') !== false) {
                 $imageUrl = trim($imageData['candidates'][0]['content']['parts'][0]['text']);
            }
        } catch (\Exception $e) {
            // If the image API call fails, we just fallback to the Gemini-guessed text URL
            error_log('Image generation API error: ' . $e->getMessage());
        }

        return [
            'description' => $result['description'] ?? '',
            'rarity' => $result['rarity'] ?? 'COMMON',
            'game' => $result['game'] ?? '',
            'imageUrl' => $imageUrl
        ];
    }
}
