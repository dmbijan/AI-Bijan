<?php

namespace Tests\Feature;

use App\Modules\Reminder\Models\Reminder;
use App\Modules\Reminder\Models\ReminderDelivery;
use App\Modules\Reminder\Models\ReminderInbox;
use App\Modules\Reminder\Models\ReminderSchedule;
use App\Modules\Reminder\Models\ReminderType;
use App\Modules\Reminder\Services\ReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Reminder & Notification Center (BAB 86 / Idea #22).
 */
class ReminderTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@reminder.my', 'Reminder Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    // -------------------------------------------------------------------
    // Katalog
    // -------------------------------------------------------------------

    public function test_jana_katalog_mencipta_jenis(): void
    {
        $s = app(ReminderService::class);

        $dicipta = $s->janaKatalog($this->t->id);

        $this->assertGreaterThan(0, $dicipta);
        $this->assertSame($dicipta, ReminderType::where('tenant_id', $this->t->id)->count());
    }

    public function test_jana_katalog_idempotent(): void
    {
        $s = app(ReminderService::class);

        $s->janaKatalog($this->t->id);
        $kedua = $s->janaKatalog($this->t->id);

        $this->assertSame(0, $kedua);
    }

    public function test_katalog_mempunyai_jenis_kritikal(): void
    {
        $this->assertArrayHasKey('payment_due', ReminderService::KATALOG);
        $this->assertArrayHasKey('invoice_overdue', ReminderService::KATALOG);
        $this->assertArrayHasKey('cod_collection', ReminderService::KATALOG);
        $this->assertArrayHasKey('compliance_deadline', ReminderService::KATALOG);
    }

    // -------------------------------------------------------------------
    // Cipta peringatan
    // -------------------------------------------------------------------

    public function test_cipta_peringatan(): void
    {
        $s = app(ReminderService::class);
        $s->janaKatalog($this->t->id);

        $r = $s->cipta($this->t->id, 'payment_due', [
            'title' => 'Bayaran AO-2026-00001',
            'due_at' => now()->addHours(6),
        ]);

        $this->assertNotNull($r->id);
        $this->assertSame(Reminder::STATUS_PENDING, $r->status);
        $this->assertSame(Reminder::PRIORITY_HIGH, $r->priority);
    }

    public function test_cipta_peringatan_guna_channels_lalai(): void
    {
        $s = app(ReminderService::class);
        $s->janaKatalog($this->t->id);

        $r = $s->cipta($this->t->id, 'invoice_overdue', []);

        $this->assertContains('in_app', $r->channels);
        $this->assertContains('email', $r->channels);
    }

    public function test_cipta_peringatan_kira_lead_hours(): void
    {
        $s = app(ReminderService::class);
        $s->janaKatalog($this->t->id);

        $r = $s->cipta($this->t->id, 'payment_due', [
            'subject_due_at' => now()->addHours(30)->toDateTimeString(),
        ]);

        // payment_due lead = 24 jam → due_at = subject_due - 24h
        $this->assertNotNull($r->due_at);
        $this->assertTrue($r->due_at->lt(now()->addHours(30)));
    }

    public function test_cipta_peringatan_polymorphic(): void
    {
        $s = app(ReminderService::class);

        $r = $s->cipta($this->t->id, 'custom_type', [
            'title' => 'Uji',
            'subject_type' => 'App\\Modules\\Orders\\Models\\UnifiedOrder',
            'subject_id' => 42,
            'subject_label' => 'AO-2026-00042',
        ]);

        $this->assertSame(42, $r->subject_id);
        $this->assertSame('AO-2026-00042', $r->subject_label);
    }

    // -------------------------------------------------------------------
    // Hantar
    // -------------------------------------------------------------------

    public function test_hantar_cipta_kotak_masuk(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji', 'due_at' => now()]);

        $hasil = $s->hantar($this->t->id, $r->id, ['in_app']);

        $this->assertSame('sent', $hasil['in_app']);
        $this->assertSame(Reminder::STATUS_SENT, $r->fresh()->status);
        $this->assertNotNull($r->fresh()->sent_at);
    }

    public function test_hantar_rekod_delivery(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji']);

        $s->hantar($this->t->id, $r->id, ['in_app', 'email']);

        $this->assertSame(2, ReminderDelivery::where('reminder_id', $r->id)->count());
    }

    public function test_hantar_in_app_cipta_inbox(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji Penting']);

        $s->hantar($this->t->id, $r->id, ['in_app']);

        $this->assertSame(1, ReminderInbox::where('reminder_id', $r->id)->count());
        $this->assertSame('Uji Penting', ReminderInbox::where('reminder_id', $r->id)->value('headline'));
    }

    // -------------------------------------------------------------------
    // Status
    // -------------------------------------------------------------------

    public function test_tunda(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji']);

        $hasil = $s->tunda($this->t->id, $r->id, 60);

        $this->assertSame(Reminder::STATUS_SNOOZED, $hasil->status);
        $this->assertNotNull($hasil->snoozed_until);
        $this->assertTrue($hasil->terbuka());
    }

    public function test_sahkan(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji']);

        $hasil = $s->sahkan($this->t->id, $r->id);

        $this->assertSame(Reminder::STATUS_ACKNOWLEDGED, $hasil->status);
        $this->assertNotNull($hasil->acknowledged_at);
    }

    public function test_lengkapkan(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji']);

        $hasil = $s->lengkapkan($this->t->id, $r->id);

        $this->assertSame(Reminder::STATUS_COMPLETED, $hasil->status);
        $this->assertNotNull($hasil->completed_at);
    }

    public function test_buang(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji']);

        $hasil = $s->buang($this->t->id, $r->id);

        $this->assertSame(Reminder::STATUS_DISMISSED, $hasil->status);
    }

    // -------------------------------------------------------------------
    // Lewat & eskalasi
    // -------------------------------------------------------------------

    public function test_lewat_dikesan(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', [
            'title' => 'Uji',
            'due_at' => now()->subDay(),
        ]);

        $this->assertTrue($r->lewat());
    }

    public function test_tidak_lewat_bila_belum_due(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', [
            'title' => 'Uji',
            'due_at' => now()->addDay(),
        ]);

        $this->assertFalse($r->lewat());
    }

    public function test_eskalasi_lewat(): void
    {
        $s = app(ReminderService::class);

        $s->cipta($this->t->id, 'test', [
            'title' => 'Lewat 1',
            'due_at' => now()->subDay(),
            'priority' => Reminder::PRIORITY_NORMAL,
        ]);

        $hasil = $s->eskalasiLewat($this->t->id);

        $this->assertSame(1, $hasil['dileskalakan']);

        $lewat = Reminder::where('tenant_id', $this->t->id)->first();
        $this->assertSame(Reminder::PRIORITY_HIGH, $lewat->priority);
        $this->assertSame(1, $lewat->escalation_count);
        $this->assertNotNull($lewat->escalated_at);
    }

    // -------------------------------------------------------------------
    // Kotak masuk AI
    // -------------------------------------------------------------------

    public function test_kotak_masuk_belum_dibaca(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji']);
        $s->hantar($this->t->id, $r->id, ['in_app']);

        $inbox = $s->kotakMasuk($this->t->id);

        $this->assertCount(1, $inbox);
    }

    public function test_tandakan_dibaca(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji']);
        $s->hantar($this->t->id, $r->id, ['in_app']);

        $item = ReminderInbox::where('tenant_id', $this->t->id)->first();
        $hasil = $s->tandakanDibaca($this->t->id, $item->id);

        $this->assertTrue($hasil->is_read);
        $this->assertNotNull($hasil->read_at);
        $this->assertCount(0, $s->kotakMasuk($this->t->id));
    }

    // -------------------------------------------------------------------
    // Jadwal
    // -------------------------------------------------------------------

    public function test_jalankan_jadual(): void
    {
        $s = app(ReminderService::class);

        ReminderSchedule::create([
            'tenant_id' => $this->t->id,
            'type_key' => 'weekly_report',
            'name' => 'Laporan Mingguan',
            'frequency' => ReminderSchedule::FREQ_WEEKLY,
            'channels' => ['in_app'],
            'is_active' => true,
            'next_run_at' => now()->subHour(),
        ]);

        $hasil = $s->jalankanJadual($this->t->id);

        $this->assertSame(1, $hasil['dijana']);
        $this->assertSame(1, Reminder::where('tenant_id', $this->t->id)->count());
    }

    public function test_kira_run_seterusnya_harian(): void
    {
        $jadual = ReminderSchedule::create([
            'tenant_id' => $this->t->id,
            'type_key' => 'x',
            'name' => 'X',
            'frequency' => ReminderSchedule::FREQ_DAILY,
            'interval_count' => 2,
        ]);

        $seterusnya = $jadual->kiraRunSeterusnya(now());

        $this->assertTrue($seterusnya->isFuture());
    }

    // -------------------------------------------------------------------
    // Ringkasan
    // -------------------------------------------------------------------

    public function test_ringkasan(): void
    {
        $s = app(ReminderService::class);

        $s->cipta($this->t->id, 'test', ['title' => 'A', 'due_at' => now()->subDay()]);
        $s->cipta($this->t->id, 'test', ['title' => 'B', 'due_at' => now()->addDay()]);

        $r = $s->ringkasan($this->t->id);

        $this->assertSame(2, $r['jumlah']);
        $this->assertSame(1, $r['lewat']);
        $this->assertArrayHasKey('ikut_prioriti', $r);
    }

    public function test_ringkasan_kosong(): void
    {
        $s = app(ReminderService::class);

        $r = $s->ringkasan($this->t->id);

        $this->assertSame(0, $r['jumlah']);
        $this->assertSame(0, $r['lewat']);
    }

    // -------------------------------------------------------------------
    // Tenant isolation
    // -------------------------------------------------------------------

    public function test_peringatan_tenant_lain_tidak_nampak(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji']);

        [$u2, $t2] = $this->daftar('lawan@reminder.my', 'Lawan Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertNull(Reminder::find($r->id));
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_jana_katalog(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/reminders/types/generate')
            ->assertOk();

        $this->assertGreaterThan(0, $res->json('data.dicipta'));
    }

    public function test_api_cipta_peringatan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/reminders', [
                'type_key' => 'payment_due',
                'title' => 'Bayaran Tertunggak',
                'due_at' => now()->addHours(2)->toDateTimeString(),
            ])
            ->assertCreated();
    }

    public function test_api_hantar(): void
    {
        $r = app(ReminderService::class)->cipta($this->t->id, 'test', ['title' => 'Uji']);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/reminders/{$r->id}/send", ['channels' => ['in_app']])
            ->assertOk();
    }

    public function test_api_tunda(): void
    {
        $r = app(ReminderService::class)->cipta($this->t->id, 'test', ['title' => 'Uji']);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/reminders/{$r->id}/snooze", ['minutes' => 60])
            ->assertOk();
    }

    public function test_api_lengkapkan(): void
    {
        $r = app(ReminderService::class)->cipta($this->t->id, 'test', ['title' => 'Uji']);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/reminders/{$r->id}/complete")
            ->assertOk();
    }

    public function test_api_inbox(): void
    {
        $s = app(ReminderService::class);
        $r = $s->cipta($this->t->id, 'test', ['title' => 'Uji']);
        $s->hantar($this->t->id, $r->id, ['in_app']);

        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/reminders/inbox/list')
            ->assertOk();
    }

    public function test_api_ringkasan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/reminders/summary')
            ->assertOk();
    }

    public function test_api_eskalasi(): void
    {
        app(ReminderService::class)->cipta($this->t->id, 'test', [
            'title' => 'Lewat',
            'due_at' => now()->subDay(),
        ]);

        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/reminders/escalate')
            ->assertOk();

        $this->assertSame(1, $res->json('data.dileskalakan'));
    }

    public function test_api_jadual(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/reminders/schedules', [
                'type_key' => 'weekly_report',
                'name' => 'Laporan Mingguan',
                'frequency' => 'weekly',
            ])
            ->assertCreated();
    }
}
