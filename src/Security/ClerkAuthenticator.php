<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class ClerkAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private ?array $cachedJwks = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private string $clerkSecretKey,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->cookies->has('__session');
    }

    public function authenticate(Request $request): Passport
    {
        $sessionToken = $request->cookies->get('__session');

        try {
            $jwks = $this->getJwks();
            $decoded = JWT::decode($sessionToken, JWK::parseKeySet($jwks));
        } catch (\Exception $e) {
            throw new AuthenticationException('Session Clerk invalide : ' . $e->getMessage());
        }

        $clerkUserId = $decoded->sub ?? null;
        if (!$clerkUserId) {
            throw new AuthenticationException('Session Clerk invalide.');
        }

        return new SelfValidatingPassport(
            new UserBadge($clerkUserId, function () use ($clerkUserId) {
                return $this->getOrCreateUser($clerkUserId);
            })
        );
    }

    private function getOrCreateUser(string $clerkUserId): User
    {
        $repository = $this->entityManager->getRepository(User::class);

        $user = $repository->findOneBy(['clerkId' => $clerkUserId]);
        if ($user) {
            return $user;
        }

        // Fetch user details from Clerk API
        $userDetails = $this->fetchClerkUser($clerkUserId);

        // Try to find existing user by email and link Clerk account
        $email = $userDetails['email'] ?? null;
        if ($email) {
            $user = $repository->findOneBy(['email' => $email]);
            if ($user) {
                $user->setClerkId($clerkUserId);
                $this->entityManager->flush();
                return $user;
            }
        }

        // Create new user
        $user = new User();
        $user->setClerkId($clerkUserId);

        if ($email) {
            $user->setEmail($email);
        } else {
            $user->setEmail('clerk_' . $clerkUserId . '@carthage-arena.local');
        }

        $username = $userDetails['username'] ?? $userDetails['first_name'] ?? 'User' . substr($clerkUserId, -6);
        $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
        $finalUsername = $baseUsername;
        $i = 1;
        while ($repository->findOneBy(['username' => $finalUsername])) {
            $finalUsername = $baseUsername . $i;
            $i++;
        }
        $user->setUsername($finalUsername);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function fetchClerkUser(string $clerkUserId): array
    {
        $ch = curl_init("https://api.clerk.dev/v1/users/{$clerkUserId}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->clerkSecretKey,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return [];
        }

        $data = json_decode($response, true);
        if (!$data) {
            return [];
        }

        $email = null;
        if (!empty($data['email_addresses'])) {
            foreach ($data['email_addresses'] as $emailEntry) {
                if (($emailEntry['id'] ?? '') === ($data['primary_email_address_id'] ?? '')) {
                    $email = $emailEntry['email_address'] ?? null;
                    break;
                }
            }
            if (!$email) {
                $email = $data['email_addresses'][0]['email_address'] ?? null;
            }
        }

        return [
            'email' => $email,
            'username' => $data['username'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'image_url' => $data['image_url'] ?? null,
        ];
    }

    private function getJwks(): array
    {
        if ($this->cachedJwks !== null) {
            return $this->cachedJwks;
        }

        // Try to read from cache file
        $cacheFile = sys_get_temp_dir() . '/clerk_jwks_cache.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if ($cached) {
                $this->cachedJwks = $cached;
                return $this->cachedJwks;
            }
        }

        $ch = curl_init('https://api.clerk.dev/v1/jwks');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->clerkSecretKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            throw new AuthenticationException('Impossible de récupérer les clés Clerk.');
        }

        $jwks = json_decode($response, true);
        if (!$jwks || !isset($jwks['keys'])) {
            throw new AuthenticationException('Format JWKS Clerk invalide.');
        }

        // Cache the JWKS
        file_put_contents($cacheFile, $response);
        $this->cachedJwks = $jwks;

        return $this->cachedJwks;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // Let the request continue
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Don't redirect on API requests or when the session is just expired
        // Just let the request continue as anonymous
        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
}
