<?php

namespace App\Modules\Prospecting\Services;

use App\Modules\Prospecting\Models\Prospect;
use App\Modules\Prospecting\Models\ProspectSource;
use Illuminate\Support\Facades\Log;

/**
 * CsvImporterService — import prospek daripada fail CSV.
 *
 * Ini adalah laluan PALING AMAN untuk prospect — pengguna sendiri memilih
 * senarai, jadi consent mengandaikan data tersebut theirs.
 *
 *iran @see ProspectingServiceProvider untuk auto-mapping kolum.
 */
class CsvImporterService
{
    public function __construct(private DedupeService $dedupe) {}

    /**
     * Petakan nama kolum CSV kepada medan prospect.
     *
     * @var array<string, string>
     */
    public const COLUMN_MAP = [
        'email' => 'email',
        'e-mail' => 'email',
        'email address' => 'email',
        'first_name' => 'first_name',
        'first name' => 'first_name',
        'nama' => 'first_name',
        'nama pertama' => 'first_name',
        'last_name' => 'last_name',
        'last name' => 'last_name',
        'nama akhir' => 'last_name',
        'name' => 'full_name',
        'nama penuh' => 'full_name',
        'phone' => 'phone',
        'mobile' => 'phone',
        'telefon' => 'phone',
        'no telefon' => 'phone',
        'no hp' => 'phone',
        'whatsapp' => 'whatsapp_number',
        'whatsapp number' => 'whatsapp_number',
        'company' => 'company',
        'syarikat' => 'company',
        'syarikat' => 'company',
        'job_title' => 'job_title',
        'title' => 'job_title',
        'jawatapan' => 'job_title',
        'website' => 'website',
        'company website' => 'website',
        'linkedin' => 'linkedin',
        'linkedin url' => 'linkedin',
        'country' => 'country',
        'negara' => 'country',
        'company_size' => 'company_size',
        'saiz syarikat' => 'company_size',
        'consent' => 'consent',
        'opt_in' => 'consent',
        'opt-in' => 'consent',
        'consent_source' => 'consent_source',
        'sumber consent' => 'consent_source',
        'consent_evidence' => 'consent_evidence',
        'bukti consent' => 'consent_evidence',
        'source' => 'source',
        'sumber' => 'source',
    ];

    /**
     * Import daripada laluan fail CSV.
     *
     * @return array{imported: int, duplicates: int, skipped: int, errors: array}
     */
    public function importFile(
        string $path,
        int $tenantId,
        ?ProspectSource $source = null,
        ?int $listId = null,
        bool $assumeConsent = false
    ): array {
        $handle = fopen($path, 'r');

        if (! $handle) {
            return ['imported' => 0, 'duplicates' => 0, 'skipped' => 0, 'errors' => ['fail tidak boleh dibuka']];
        }

        // Deteksi delimiter (koma atau koma bertitik)
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = $firstLine && substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $header = fgetcsv($handle, 0, $delimiter);

        if (! $header) {
            fclose($handle);

            return ['imported' => 0, 'duplicates' => 0, 'skipped' => 0, 'errors' => ['fail kosong']];
        }

        // Normalkan header
        $header = array_map(function ($h) {
            return strtolower(trim((string) $h));
        }, $header);

        // Deteksi pemetaan kolum
        $mapping = $this->petakanKolum($header);

        if (! isset($mapping['email']) && ! isset($mapping['phone'])) {
            fclose($handle);

            return [
                'imported' => 0, 'duplicates' => 0, 'skipped' => 0,
                'errors' => ['tiada kolum e-mel atau telefon dikesan. Lajur dijumpai: '.implode(', ', $header)],
            ];
        }

        $imported = 0;
        $duplicates = 0;
        $skipped = 0;
        $errors = [];
        $row = 0;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row++;

            if (count($data) < 2) {
                $skipped++;

                continue;
            }

            $record = $this->barisKeRecord(array_combine($header, array_pad($data, count($header), null)));

            if (! $record) {
                $skipped++;

                continue;
            }

            // Consent: kolum CSV ATAUandaan assumeConsent
            $consentSource = $record['consent_source'] ?? null;
            $consentEvidence = $record['consent_evidence'] ?? null;
            $consentValue = $record['consent'] ?? null;

            $hasConsent = $assumeConsent
                || in_array((string) $consentValue, ['1', 'true', 'yes', 'y', 'ya', 'benar', 'granted'], true);

            $consentEvidence = $consentEvidence ?: ($hasConsent ? 'import-csv:'.$source?->id : null);
            $consentSource = $consentSource ?: ($hasConsent ? 'csv_import' : null);

            unset($record['consent'], $record['consent_source'], $record['consent_evidence'], $record['source']);

            $record['source_id'] = $source?->id;
            $record['list_id'] = $listId;
            $record['consent_status'] = $hasConsent ? Prospect::CONSENT_GRANTED : Prospect::CONSENT_UNKNOWN;
            $record['consent_at'] = $hasConsent ? now() : null;
            $record['consent_evidence'] = $consentEvidence;
            $record['consent_source'] = $consentSource;
            $record['country'] = $record['country'] ?? 'MY';
            $record['status'] = Prospect::STATUS_NEW;

            try {
                $result = $this->dedupe->simpanDenganSemakanDupe($tenantId, $record);

                if ($result['created']) {
                    $imported++;
                } else {
                    $duplicates++;
                }
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Baris {$row}: ".$e->getMessage();
            }
        }

        fclose($handle);

        Log::info('CSV import selesai', [
            'tenant_id' => $tenantId,
            'imported' => $imported,
            'duplicates' => $duplicates,
            'skipped' => $skipped,
        ]);

        return compact('imported', 'duplicates', 'skipped', 'errors');
    }

    /**
     * Kesan pemetaan kolum.
     *
     * @return array<string, string>
     */
    public function petakanKolum(array $header): array
    {
        $mapping = [];

        foreach ($header as $col) {
            $key = self::COLUMN_MAP[$col] ?? null;
            if ($key && ! isset($mapping[$key])) {
                $mapping[$key] = $col;
            }
        }

        return $mapping;
    }

    /**
     * Tukar baris CSV kepada array data prospect.
     */
    private function barisKeRecord(array $row): ?array
    {
        $get = function (string $key) use ($row) {
            return isset($row[$key]) && $row[$key] !== '' ? trim((string) $row[$key]) : null;
        };

        $email = $get('email');
        $phone = $get('phone');
        $fullName = $get('full_name');

        if (! $email && ! $phone && ! $fullName) {
            return null;
        }

        // E-mel tidak valid → tolak baris ini
        if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $firstName = $get('first_name');
        $lastName = $get('last_name');

        // Jika hanya ada nama penuh, pecahkan
        if (! $firstName && ! $lastName && $fullName) {
            $parts = preg_split('/\s+/', $fullName, 2);
            $firstName = $parts[0] ?? null;
            $lastName = $parts[1] ?? null;
        }

        return array_filter([
            'email' => $email,
            'phone' => $phone,
            'whatsapp_number' => $get('whatsapp_number'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'company' => $get('company'),
            'job_title' => $get('job_title'),
            'website' => $get('website'),
            'linkedin' => $get('linkedin'),
            'country' => $get('country'),
            'company_size' => $get('company_size'),
            'consent' => $get('consent'),
            'consent_source' => $get('consent_source'),
            'consent_evidence' => $get('consent_evidence'),
            'source' => $get('source'),
        ], fn ($v) => $v !== null);
    }
}
