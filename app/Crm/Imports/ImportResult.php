<?php

namespace App\Crm\Imports;

readonly class ImportResult
{
    public function __construct(
        public int $batchId,
        public string $sourceSha256,
        public int $verifiedRows,
        public int $siteCount,
        public int $contactCount,
        public int $namelessContactExceptions,
        public int $unresolvedOwners,
        public bool $alreadyImported,
    ) {}
}
