<?php

namespace App\Validator\Constraints;

use App\Repository\LicenseRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidLicenseValidator extends ConstraintValidator
{
    public function __construct(
        private LicenseRepository $licenseRepository
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidLicense) {
            throw new UnexpectedTypeException($constraint, ValidLicense::class);
        }

        // Allow null/empty values (use NotBlank for required validation)
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        // Check if license exists and is available
        $license = $this->licenseRepository->findAvailableByCode($value);

        if ($license === null) {
            // Check if it exists but is used
            $usedLicense = $this->licenseRepository->findOneBy(['licenseCode' => $value]);
            
            if ($usedLicense !== null && $usedLicense->isUsed()) {
                $this->context->buildViolation($constraint->alreadyUsedMessage)
                    ->setParameter('{{ value }}', $value)
                    ->addViolation();
            } else {
                $this->context->buildViolation($constraint->notFoundMessage)
                    ->setParameter('{{ value }}', $value)
                    ->addViolation();
            }
        }
    }
}
