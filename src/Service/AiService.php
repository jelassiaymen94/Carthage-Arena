<?php

namespace App\Service;

use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $endpoint;

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        // use Groq AI key instead of OpenAI
        $this->apiKey = $_ENV['GROQ_API_KEY'] ?? '';
        if (empty($this->apiKey)) {
            throw new RuntimeException('GROQ_API_KEY environment variable is not set');
        }
        // allow the base URL to be configured in env
        // default to the provided GROQ URL
        $this->endpoint = rtrim($_ENV['GROQ_API_URL'] ?? 'https://api.groq.com/openai/v1', '/');
    }

    public function generateMerchDescription(string $name, string $type, int $price): string
    {
        // endpoint changed for Groq AI service (configurable via GROQ_API_URL)
        // the endpoint should include any version segment; we only append the resource path
        $url = rtrim($this->endpoint, '/') . '/chat/completions';

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Create an epic gaming marketplace description for a $type item called $name priced at $price coins."
                        ]
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            // surface DNS/resolution errors nicely
            throw new RuntimeException('Groq API request failed: ' . $e->getMessage());
        }

        $data = $response->toArray();
        return $data['choices'][0]['message']['content'];
    }

    public function calculateDynamicPrice(int $basePrice, int $stock): float
    {
        $rarity = $stock < 10 ? 0.5 : 0.1;
        $trend = rand(1, 100) / 100;

        return $basePrice * (1 + $rarity + $trend);
    }

    public function fraudScore(int $quantity, int $totalPrice): float
    {
        if ($quantity > 10) {
            return 0.9;
        }
        if ($totalPrice > 10000) {
            return 0.8;
        }
        return 0.1;
    }
}
