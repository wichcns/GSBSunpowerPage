<?php

namespace App\Crm\Imports;

readonly class LegacyExport
{
    public function __construct(
        public string $sourceSha256,
        public string $exportedAt,
        public array $tables,
        public array $counts,
    ) {}

    public function totalRows(): int
    {
        return array_sum($this->counts);
    }

    public function namedContacts(): int
    {
        return count(array_filter($this->tables['leads'], fn (array $lead) => trim((string) ($lead['contact_name'] ?? '')) !== ''));
    }

    public function namelessContactExceptions(): int
    {
        return count(array_filter($this->tables['leads'], fn (array $lead) => trim((string) ($lead['contact_name'] ?? '')) === '' &&
            (trim((string) ($lead['phone'] ?? '')) !== '' ||
                trim((string) ($lead['line_name'] ?? '')) !== '' ||
                trim((string) ($lead['email'] ?? '')) !== '')
        ));
    }

    public function unresolvedOwners(): int
    {
        return count(array_filter($this->tables['leads'], fn (array $lead) => trim((string) ($lead['assigned_to'] ?? '')) !== ''));
    }
}
