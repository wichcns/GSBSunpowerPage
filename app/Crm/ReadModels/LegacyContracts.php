<?php

namespace App\Crm\ReadModels;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class LegacyContracts
{
    private const FIELDS = ['contract_status', 'contract_term', 'contract_expiry', 'rent_free_months', 'commercial_model', 'rental_cost', 'parking_spaces', 'contract_signed_at', 'contract_effective_at', 'monthly_payment_day'];

    private const ELIGIBLE_STAGES = ['INTERNAL_APPROVED', 'MOU_SIGNED', 'CONTRACT_SIGNED'];

    public function __construct(private readonly LegacyPipeline $pipeline) {}

    public function all(): array
    {
        $pipelineRows = $this->pipeline->all();
        if ($pipelineRows === []) {
            return [];
        }

        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        $records = ($batchId === null ? collect() : DB::table('crm_legacy_records')->where('batch_id', $batchId)
            ->where('source_table', 'leads')->get(['source_id', 'payload']))
            ->concat(DB::table('crm_native_opportunities')->get(['source_id', 'payload']));
        $current = DB::table('crm_business_terms')->whereIn('lead_source_id', array_keys($pipelineRows))
            ->get(['lead_source_id', 'values', 'version'])->keyBy('lead_source_id');
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
            $hasDetails = collect(array_intersect_key($values, array_flip(['contract_status', 'contract_term', 'contract_expiry', 'rent_free_months', 'contract_signed_at', 'contract_effective_at', 'monthly_payment_day'])))
                ->contains(fn ($value) => $value !== null && trim((string) $value) !== '');
            $pipeline = $pipelineRows[$record->source_id];
            if (! in_array($pipeline['stage_code'], self::ELIGIBLE_STAGES, true)) {
                continue;
            }

            $missingFields = [];
            foreach (['contract_status', 'contract_term', 'contract_expiry'] as $field) {
                if ($values[$field] === null || trim((string) $values[$field]) === '') {
                    $missingFields[] = $field;
                }
            }
            $documentStatus = $uploadedDocuments->has($record->source_id) ? 'current'
                : (isset($legacyDocuments[$record->source_id]) ? 'legacy_metadata' : 'not_found');

            $rows[$record->source_id] = array_replace($pipeline, $values, [
                'expiry_class' => $this->expiryClass($values['contract_expiry'], $today),
                'contract_source' => $override === null ? 'legacy' : 'current',
                'missing_contract_details' => ! $hasDetails,
                'missing_fields' => $missingFields,
                'document_status' => $documentStatus,
                'business_version' => $override?->version ?? 0,
            ]);
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
