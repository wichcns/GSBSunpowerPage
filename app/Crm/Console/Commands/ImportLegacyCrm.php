<?php

namespace App\Crm\Console\Commands;

use App\Crm\Imports\LegacyExportReader;
use App\Crm\Imports\LegacyImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ImportLegacyCrm extends Command
{
    protected $signature = 'crm:import-legacy
        {--source= : Path to database.json}
        {--manifest= : Path to EXPORT_MANIFEST.json}
        {--expected-sha256= : Exact source file SHA-256, required for import}
        {--import : Write to the approved local database after validation}';

    protected $description = 'Validate or import the legacy CRM full export into the local CRM database';

    public function handle(LegacyExportReader $reader, LegacyImportService $service): int
    {
        if (config('database.crm_enabled')) {
            DB::setDefaultConnection('crm');
        }

        $source = (string) $this->option('source');
        $manifest = (string) $this->option('manifest');
        $expectedSha = $this->option('expected-sha256');
        $write = (bool) $this->option('import');

        if ($source === '' || $manifest === '') {
            $this->error('Both --source and --manifest are required.');

            return self::FAILURE;
        }
        if ($write && (! is_string($expectedSha) || ! preg_match('/^[a-fA-F0-9]{64}$/', $expectedSha))) {
            $this->error('--expected-sha256 with 64 hex characters is required for --import.');

            return self::FAILURE;
        }

        try {
            $export = $reader->read($source, $manifest, $expectedSha ?: null);
            $this->line('Source SHA-256: '.$export->sourceSha256);
            foreach ($export->counts as $table => $count) {
                $this->line($table.': '.$count);
            }
            $this->line('Total source rows: '.$export->totalRows());
            $this->line('Site projections: '.count($export->tables['leads']));
            $this->line('Named contacts: '.$export->namedContacts());
            $this->line('Nameless contact exceptions: '.$export->namelessContactExceptions());
            $this->line('Unmapped legacy owner labels: '.$export->unresolvedOwners());

            if (! $write) {
                $this->info('Dry-run complete. No database writes were made.');

                return self::SUCCESS;
            }

            $result = $service->import($export, $source);
            $this->info($result->alreadyImported ? 'Source was already imported and passed verification.' : 'Import committed and verified.');
            $this->line('Batch ID: '.$result->batchId);
            $this->line('Verified raw rows: '.$result->verifiedRows);

            return self::SUCCESS;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error('Import failed safely: '.$exception::class.'. No success reported.');

            return self::FAILURE;
        }
    }
}
