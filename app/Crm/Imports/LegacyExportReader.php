<?php

namespace App\Crm\Imports;

use JsonException;
use RuntimeException;

class LegacyExportReader
{
    public const TABLES = ['activities', 'attachments', 'integration_events', 'leads', 'projects', 'users', 'visit_logs'];

    public function read(string $sourcePath, string $manifestPath, ?string $expectedSha256 = null): LegacyExport
    {
        if (! is_file($sourcePath) || ! is_file($manifestPath)) {
            throw new RuntimeException('Source or manifest file is missing.');
        }

        $sha = hash_file('sha256', $sourcePath);
        if ($expectedSha256 !== null && ! hash_equals(strtolower($expectedSha256), $sha)) {
            throw new RuntimeException('Source SHA-256 does not match the expected value.');
        }

        try {
            $source = json_decode(file_get_contents($sourcePath), true, 512, JSON_THROW_ON_ERROR);
            $manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Source or manifest contains malformed JSON.', 0, $exception);
        }

        if (! is_array($source) || ! is_array($manifest) || ! isset($source['tables'], $source['exportedAt'], $manifest['tableCounts']) ||
            ! is_array($source['tables']) || ! is_array($manifest['tableCounts'])) {
            throw new RuntimeException('Source or manifest structure is invalid.');
        }

        $sourceTables = array_keys($source['tables']);
        $manifestTables = array_keys($manifest['tableCounts']);
        sort($sourceTables);
        sort($manifestTables);
        if ($sourceTables !== self::TABLES || $manifestTables !== self::TABLES) {
            throw new RuntimeException('Unexpected or missing source table.');
        }

        $counts = [];
        foreach (self::TABLES as $table) {
            $rows = $source['tables'][$table];
            if (! is_array($rows) || ! array_is_list($rows)) {
                throw new RuntimeException("Invalid rows in table {$table}.");
            }

            $seen = [];
            foreach ($rows as $row) {
                if (! is_array($row) || ! is_string($row['id'] ?? null) || trim($row['id']) === '') {
                    throw new RuntimeException("Missing source ID in table {$table}.");
                }
                if (isset($seen[$row['id']])) {
                    throw new RuntimeException("Found duplicate source ID in table {$table}.");
                }
                $seen[$row['id']] = true;
            }

            $counts[$table] = count($rows);
            if (! is_int($manifest['tableCounts'][$table]) || $manifest['tableCounts'][$table] !== $counts[$table]) {
                throw new RuntimeException("Manifest count mismatch for table {$table}.");
            }
        }

        $leadIds = array_fill_keys(array_column($source['tables']['leads'], 'id'), true);
        $visitIds = array_fill_keys(array_column($source['tables']['visit_logs'], 'id'), true);
        foreach (['activities', 'projects', 'visit_logs', 'integration_events', 'attachments'] as $table) {
            foreach ($source['tables'][$table] as $row) {
                $leadId = $row['lead_id'] ?? null;
                if (($table !== 'integration_events' || $leadId !== null) && (! is_string($leadId) || ! isset($leadIds[$leadId]))) {
                    throw new RuntimeException("Broken lead reference in table {$table}.");
                }
                if ($table === 'attachments' && isset($row['visit_log_id']) && ! isset($visitIds[$row['visit_log_id']])) {
                    throw new RuntimeException('Broken visit reference in table attachments.');
                }
            }
        }

        foreach ($source['tables']['leads'] as $lead) {
            if (! is_string($lead['site_name'] ?? null) || trim($lead['site_name']) === '') {
                throw new RuntimeException('A lead has no site name.');
            }
        }

        return new LegacyExport($sha, (string) $source['exportedAt'], $source['tables'], $counts);
    }
}
