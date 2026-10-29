<?php

namespace App\Crm\Imports;

readonly class VerificationReport
{
    public function __construct(
        public bool $valid,
        public int $verifiedRows,
        public array $errors,
    ) {}
}
