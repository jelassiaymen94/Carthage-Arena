<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerificationService
{
    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
    ) {}

    /**
     * Generate a signed verification URL for the given user.
     */
    public function generateSignature(User $user, string $verifyUrl): \SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents
    {
        return $this->verifyEmailHelper->generateSignature(
            'app_verify_email',
            (string) $user->getId(),
            $user->getEmail(),
            ['id' => (string) $user->getId()]
        );
    }

    /**
     * Validate the signed URL from the request.
     *
     * @throws VerifyEmailExceptionInterface
     */
    public function validateRequest(User $user, Request $request): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation(
            $request->getUri(),
            (string) $user->getId(),
            $user->getEmail()
        );
    }
}
