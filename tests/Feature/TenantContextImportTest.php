<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Pengawal regresi: mana-mana fail yang guna TenantContext sebagai kelas
 * (TenantContext::...) MESTI ada import `use App\Support\Tenancy\TenantContext;`
 * ATAU guna nama penuh (FQN). Tanpa itu, PHP tafsir nama ke namespace semasa
 * (mis. App\Modules\CRM\Http\Controllers\TenantContext) yang tak wujud, lalu
 * setiap endpoint yang jalankannya 500 (BindingResolutionException).
 *
 * Tujuan: kekal 0 selama-lamanya.
 */
class TenantContextImportTest extends TestCase
{
    #[Test]
    public function tiada_fail_guna_tenantcontext_tanpa_import(): void
    {
        $appDir = dirname(__DIR__, 2).'/app';
        $pelanggar = [];

        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($appDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($rii as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $code = file_get_contents($file->getPathname());

            // Tidak guna TenantContext sebagai kelas? Abaikan.
            if (! preg_match('/\bTenantContext::/', $code)) {
                continue;
            }

            $adaImport = str_contains($code, 'use App\\Support\\Tenancy\\TenantContext;');
            $adaFqn = (bool) preg_match('/App\\\\Support\\\\Tenancy\\\\TenantContext::/', $code);

            if (! $adaImport && ! $adaFqn) {
                $pelanggar[] = str_replace($appDir.'/', '', $file->getPathname());
            }
        }

        $this->assertSame(
            [],
            $pelanggar,
            "Fail berikut guna TenantContext tanpa import/FQN (setiap endpointnya akan 500):\n  "
                .implode("\n  ", $pelanggar)
        );
    }
}
