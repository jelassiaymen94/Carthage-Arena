<?php

namespace App\Message;

class DeliverSkinMessage
{
    public function __construct(
        public readonly string $skinId,
        public readonly string $userId,
    ) {}
}
