<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiBioController extends AbstractController
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    #[Route('/api/generate-bio', name: 'app_ai_generate_bio', methods: ['POST'])]
    public function generateBio(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifi├®.'], 401);
        }

        $apiKey = $_ENV['NVIDIA_API_KEY'] ?? '';
        if (!$apiKey) {
            return $this->json(['error' => 'Cl├® API manquante.'], 500);
        }

        $profile = $user->getProfile();
        $name    = $user->getName();
        $role    = in_array('ROLE_ADMIN', $user->getRoles()) ? 'administrateur' : 'joueur';
        $bio     = $profile ? ($profile->getBio() ?? '') : '';

        $prompt = <<<PROMPT
Tu es un assistant sp├®cialis├® dans la cr├®ation de biographies de joueurs pour une plateforme d'esport tunisienne nomm├®e Carthage Arena.

G├®n├¿re une courte biographie de joueur en fran├ºais (max 300 caract├¿res) pour le joueur suivant :
- Nom : {$name}
- R├┤le : {$role}
- Biographie actuelle (peut ├¬tre vide) : {$bio}

La biographie doit ├¬tre enthousiaste, dynamique, ├á la premi├¿re personne, et mettre en avant la passion pour le gaming comp├®titif. Ne d├®passe pas 300 caract├¿res. R├®ponds uniquement avec la biographie, sans guillemets ni explication.
PROMPT;

        try {
            $response = $this->httpClient->request('POST', 'https://integrate.api.nvidia.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'meta/llama-3.1-8b-instruct',
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.8,
                    'top_p'       => 1,
                    'max_tokens'  => 300,
                    'stream'      => false,
                ],
                'timeout'      => 60,
                'max_duration' => 90,
            ]);

            $data = $response->toArray();
            $bio  = trim($data['choices'][0]['message']['content'] ?? '');

            // Strip any surrounding quotes the model might add
            $bio = trim($bio, '"\'');

            // Enforce 500 char limit from Profile entity
            if (mb_strlen($bio) > 500) {
                $bio = mb_substr($bio, 0, 497) . '...';
            }

            return $this->json(['bio' => $bio]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la g├®n├®ration : ' . $e->getMessage()], 500);
        }
    }
}
