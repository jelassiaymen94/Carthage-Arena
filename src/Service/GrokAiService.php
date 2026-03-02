<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GrokAiService
{
    private string $apiKey;
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(
        string $xaiApiKey,
        HttpClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->apiKey = $xaiApiKey;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function chat(array $messages): string
    {
        if (empty($this->apiKey)) {
            return "Désolé, l'assistant n'est pas configuré.";
        }

        try {
            $response = $this->httpClient->request('POST', 'https://api.x.ai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'messages' => array_merge([
                        [
                            'role' => 'system',
                            'content' => 'Tu es un assistant spécialisé pour Carthage Arena, une plateforme de tournois de jeux vidéo (Esports). IMPORTANT : Tu ne dois répondre QU\'AUX questions liées à Carthage Arena, au gaming, aux tournois, ou au support technique de la plateforme. Si l\'utilisateur pose une question sur un autre sujet (cuisine, politique, histoire générale, etc.), réponds poliment que tu es uniquement là pour aider sur Carthage Arena et le gaming. Sois concis, amical et passionné de gaming.'
                        ]
                    ], $messages),
                    'model' => 'grok-2-latest',
                    'stream' => false,
                    'temperature' => 0.3
                ],
            ]);

            $result = $response->toArray();
            return $result['choices'][0]['message']['content'] ?? "Je n'ai pas pu générer de réponse.";

        } catch (\Exception $e) {
            $this->logger->error("Grok AI Error: " . $e->getMessage());
            return "Une erreur est survenue lors de la communication avec Grok.";
        }
    }
}
