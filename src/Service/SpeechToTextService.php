<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class SpeechToTextService
{
    private string $apiKey;
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(
        string $nvidiaApiKey,
        HttpClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->apiKey = $nvidiaApiKey;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Transcribes audio file using NVIDIA Canary-1B model
     * Supports various formats (wav, mp3, flac, etc.)
     */
    public function transcribe(string $filePath): string
    {
        if (empty($this->apiKey)) {
            return "Erreur : Clé API NVIDIA non configurée.";
        }

        try {
            // Read file content
            if (!file_exists($filePath)) {
                throw new \Exception("Fichier audio introuvable.");
            }

            $response = $this->httpClient->request('POST', 'https://integrate.api.nvidia.com/v1/audio/transcriptions', [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                ],
                'extra' => [
                    'files' => [
                        'file' => fopen($filePath, 'r'),
                    ],
                ],
                'body' => [
                    'model' => 'nvidia/canary-1b',
                    'language' => 'fr', // Force French for better accuracy on this platform
                    'response_format' => 'json',
                ],
            ]);

            $result = $response->toArray();
            return $result['text'] ?? "Impossible de transcrire l'audio.";

        } catch (\Exception $e) {
            $this->logger->error("STT Service Error: " . $e->getMessage());
            return "Erreur lors de la transcription : " . $e->getMessage();
        }
    }
}
