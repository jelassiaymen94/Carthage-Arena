<?php

namespace App\Security;

use App\Entity\Profile;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

class DiscordAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $em,
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
        private readonly AuthService $authService,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_discord_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('discord');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var DiscordResourceOwner $discordUser */
                $discordUser = $client->fetchUserFromToken($accessToken);

                $discordId = $discordUser->getId();
                $email     = $discordUser->getEmail();
                $username  = $discordUser->getUsername();
                $avatar    = $discordUser->getAvatarHash()
                    ? sprintf('https://cdn.discordapp.com/avatars/%s/%s.png', $discordId, $discordUser->getAvatarHash())
                    : null;

                // 1. Try to find by Discord ID
                $user = $this->userRepository->findOneBy(['discordId' => $discordId]);

                // 2. Fall back to email match (link existing account)
                if (!$user && $email) {
                    $user = $this->userRepository->findOneBy(['email' => $email]);
                    if ($user) {
                        $user->setDiscordId($discordId);
                    }
                }

                // 3. Auto-register new user
                if (!$user) {
                    $user = new User();
                    $user->setDiscordId($discordId);

                    // Ensure unique email
                    if ($email) {
                        $user->setEmail($email);
                    } else {
                        $user->setEmail($discordId . '@discord.invalid');
                    }

                    // Ensure unique username (append discriminator suffix if taken)
                    $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', $username) ?: 'user';
                    $baseUsername = substr($baseUsername, 0, 45);
                    $finalUsername = $baseUsername;
                    $suffix = 1;
                    while ($this->userRepository->findOneBy(['username' => $finalUsername])) {
                        $finalUsername = $baseUsername . $suffix++;
                    }
                    $user->setUsername($finalUsername);

                    // Password not needed for OAuth users — set a random unusable one
                    $user->setPassword(bin2hex(random_bytes(32)));

                    // Discord users are considered verified (authenticated via Discord)
                    $user->setIsVerified(true);

                    // Create linked Profile
                    $profile = new Profile();
                    $profile->setUser($user);
                    if ($avatar) {
                        $profile->setAvatarUrl($avatar);
                    }
                    $this->em->persist($profile);
                    $this->em->persist($user);
                    $this->em->flush();
                }

                // Update avatar from Discord if not set
                if ($avatar && $user->getProfile() && !$user->getProfile()->getAvatarUrl()) {
                    $user->getProfile()->setAvatarUrl($avatar);
                    $this->em->flush();
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var User $user */
        $user = $token->getUser();

        // Enforce single-session via AuthToken
        $this->authService->authenticate($user);

        return new RedirectResponse($this->router->generate('app_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set('discord_auth_error', strtr($exception->getMessageKey(), $exception->getMessageData()));

        return new RedirectResponse($this->router->generate('app_login'));
    }
}
