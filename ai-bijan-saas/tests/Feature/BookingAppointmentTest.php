<?php

namespace Tests\Feature;

use App\Modules\CRM\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

class BookingAppointmentTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
    }

    #[Test]
    public function temujanji_dengan_pelanggan_boleh_disimpan(): void
    {
        [$u, $t] = $this->daftar('salon@cantik.my', 'Salon Cantik');
        $this->dalamTenant($t);
        $pelanggan = Customer::create(['tenant_id' => $t->id, 'customer_code' => 'C001', 'name' => 'Puan Aminah']);

        $http = $this->sebagai($u, $t);
        $servis = $http->postJson('/api/v1/booking/services', ['name' => 'Gunting Rambut', 'duration_minutes' => 30, 'price' => 25])->assertCreated();

        $r = $http->postJson('/api/v1/booking/appointments', [
            'service_id' => $servis->json('id'),
            'customer_id' => $pelanggan->id,
            'start_time' => now()->addDay()->setTime(10, 0)->toDateTimeString(),
            'end_time' => now()->addDay()->setTime(10, 30)->toDateTimeString(),
        ])->assertCreated();

        $this->assertSame($pelanggan->id, (int) $r->json('customer_id'));
        $this->assertSame($t->id, (int) $r->json('tenant_id'));
    }
}
