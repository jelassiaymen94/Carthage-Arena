<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidLicense extends Constraint
{
    public string $message = 'Le numéro de licence "{{ value }}" n\'est pas valide ou a déjà été utilisé.';
    public string $notFoundMessage = 'Le numéro de licence "{{ value }}" n\'existe pas.';
    public string $alreadyUsedMessage = 'Le numéro de licence "{{ value }}" a déjà été utilisé.';

    public function __construct(
        ?string $message = null,
        ?string $notFoundMessage = null,
        ?string $alreadyUsedMessage = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);

        $this->message = $message ?? $this->message;
        $this->notFoundMessage = $notFoundMessage ?? $this->notFoundMessage;
        $this->alreadyUsedMessage = $alreadyUsedMessage ?? $this->alreadyUsedMessage;
    }
}
