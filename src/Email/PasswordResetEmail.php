<?php

namespace App\Email;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class PasswordResetEmail extends TemplatedEmail
{
    public function __construct(User $user, string $resetUrl)
    {
        parent::__construct();

        $this
            ->from(new Address('no-reply@demomailtrap.co', 'Carthage Arena'))
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Réinitialisation de votre mot de passe — Carthage Arena')
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context([
                'user' => $user,
                'resetUrl' => $resetUrl,
                'expiresInMinutes' => 60,
            ]);
    }
}
