<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class DiscordController extends AbstractController
{
    #[Route('/connect/discord', name: 'connect_discord')]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('discord')
            ->redirect(['identify', 'email'], []);
    }

    #[Route('/connect/discord/check', name: 'connect_discord_check')]
    public function connectCheckAction(): never
    {
        // This route is handled entirely by DiscordAuthenticator.
        // Symfony will never reach this point.
        throw new \LogicException('This should never be reached.');
    }
}
