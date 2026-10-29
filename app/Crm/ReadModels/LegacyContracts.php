<?php

namespace App\Crm\ReadModels;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class LegacyContracts
{
    private const FIELDS = ['contract_status', 'contract_term', 'contract_expiry', 'rent_free_months'];

    public function __construct(private readonly LegacyPipeline $pipeline) {}

    public function all(): array
    {
        $pipelineRows = $this->pipeline->all();
        if ($pipelineRows === []) {
            return [];
        }

        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        $records = DB::table('crm_legacy_records')->where('batch_id', $batchId)
            ->where('source_table', 'leads')->get(['source_id', 'payload']);
        $current = DB::table('crm_business_terms')->whereIn('lead_source_id', array_keys($pipelineRows))
            ->get(['lead_source_id', 'values'])->keyBy('lead_source_id');
        $uploadedDocuments = DB::table('crm_attachments')->whereIn('lead_source_id', array_keys($pipelineRows))
            ->where('category', 'CONTRACT')->pluck('lead_source_id')->flip();
        $legacyDocuments = [];
        foreach (DB::table('crm_legacy_records')->where('batch_id', $batchId)
            ->where('source_table', 'attachments')->get(['payload']) as $attachment) {
            $metadata = json_decode($attachment->payload, true, 512, JSON_THROW_ON_ERROR);
            if (strtoupper(trim((string) ($metadata['category'] ?? ''))) === 'CONTRACT'
                && isset($pipelineRows[$metadata['lead_id'] ?? ''])) {
                $legacyDocuments[$metadata['lead_id']] = true;
            }
        }
        $today = CarbonImmutable::now('Asia/Bangkok')->startOfDay();
        $rows = [];

        foreach ($records as $record) {
            if (! isset($pipelineRows[$record->source_id])) {
                continue;
            }
            $legacy = json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);
            $values = array_fill_keys(self::FIELDS, null);
            $values = array_replace($values, array_intersect_key($legacy, $values));
            $override = $current->get($record->source_id);
            if ($override !== null) {
                $edited = json_decode($override->values, true, 512, JSON_THROW_ON_ERROR);
                $values = array_replace($values, array_intersect_key($edited, $values));
            }
            $hasDetails = collect($values)->contains(fn ($value) => $value !== null && trim((string) $value) !== '');
            $pipeline = $pipelineRows[$record->source_id];
            if (! $hasDetails && ! in_array($pipeline['stage_code'], ['CONTRACT', 'SIGNED'], true)) {
                continue;
            }

            $missingFields = [];
            foreach (['contract_status' => 'สถานะสัญญา', 'contract_term' => 'อายุสัญญา', 'contract_expiry' => 'วันหมดอายุ'] as $field => $label) {
                if ($values[$field] === null || trim((string) $values[$field]) === '') {
                    $missingFields[] = $label;
                }
            }
            $documentStatus = $uploadedDocuments->has($record->source_id) ? 'current'
                : (isset($legacyDocuments[$record->source_id]) ? 'legacy_metadata' : 'not_found');

            $rows[$record->source_id] = $pipeline + $values + [
                'expiry_class' => $this->expiryClass($values['contract_expiry'], $today),
                'contract_source' => $override === null ? 'legacy' : 'current',
                'missing_contract_details' => ! $hasDetails,
                'missing_fields' => $missingFields,
                'document_status' => $documentStatus,
            ];
        }

        return $rows;
    }

    private function expiryClass(mixed $date, CarbonImmutable $today): string
    {
        if ($date === null || trim((string) $date) === '') {
            return 'missing';
        }
        if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return 'invalid';
        }
        try {
            $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $date, 'Asia/Bangkok');
        } catch (\Throwable) {
            return 'invalid';
        }
        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            return 'invalid';
        }
        if ($parsed->lessThan($today)) {
            return 'expired';
        }

        return $parsed->lessThanOrEqualTo($today->addDays(90)) ? 'next_90' : 'future';
    }
}
