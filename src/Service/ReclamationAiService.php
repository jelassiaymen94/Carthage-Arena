<?php

namespace App\Service;

use App\Entity\Reclamation;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

class ReclamationAiService
{
    private string $apiKey;
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private LoggerInterface $logger;

    public function __construct(
        string $nvidiaApiKey,
        HttpClientInterface $httpClient,
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->apiKey = $nvidiaApiKey;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param Reclamation[] $reclamations
     */
    public function getSummary(array $reclamations): string
    {
        if (empty($reclamations)) {
            return "Aucune réclamation à analyser.";
        }

        // Cache based on the number of reclamations and the latest ID to ensure updates
        $latestId = $reclamations[0]->getId() ?? 0;
        $cacheKey = 'reclamation_ai_summary_' . count($reclamations) . '_' . $latestId;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($reclamations) {
            $item->expiresAfter(3600); // 1 hour

            if (empty($this->apiKey)) {
                return "Indisponible : Clé API non configurée.";
            }

            $data = [];
            foreach (array_slice($reclamations, 0, 10) as $reclamation) {
                $data[] = sprintf(
                    "[%s] %s: %s",
                    $reclamation->getCategory()->getLabel(),
                    $reclamation->getSubject(),
                    substr($reclamation->getMessage(), 0, 100) . "..."
                );
            }

            $prompt = "Voici les 10 dernières réclamations d'utilisateurs sur notre plateforme Carthage Arena :\n" .
                implode("\n", $data) .
                "\n\nEn tant qu'expert en support client, donne-moi un résumé très court (max 2 phrases) des tendances actuelles et une recommandation prioritaire.";

            try {
                $response = $this->httpClient->request('POST', 'https://integrate.api.nvidia.com/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => "Bearer {$this->apiKey}",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => 'meta/llama-3.1-405b-instruct',
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'temperature' => 0.2,
                        'top_p' => 0.7,
                        'max_tokens' => 512,
                    ],
                ]);

                $result = $response->toArray();
                return $result['choices'][0]['message']['content'] ?? "Impossible de générer le résumé.";

            } catch (\Exception $e) {
                $this->logger->error("AI Analytics Error: " . $e->getMessage());
                return "Erreur d'analyse IA. Veuillez réessayer plus tard.";
            }
        });
    }
}
