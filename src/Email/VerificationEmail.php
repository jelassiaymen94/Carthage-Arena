<?php

namespace App\Email;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class VerificationEmail extends TemplatedEmail
{
    public function __construct(User $user, string $verifyUrl)
    {
        parent::__construct();

        $this
            ->from(new Address('no-reply@demomailtrap.co', 'Carthage Arena'))
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('V├®rifiez votre adresse e-mail ÔÇö Carthage Arena')
            ->htmlTemplate('emails/verification.html.twig')
            ->context([
                'user' => $user,
                'verifyUrl' => $verifyUrl,
            ]);
    }
}
