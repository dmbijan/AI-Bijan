<?php

namespace App\Modules\AIAgents\Tools;

use App\Modules\CRM\Models\Customer;

class PelangganCari extends AgentTool
{
    public function name(): string { return 'pelanggan.cari'; }

    public function description(): string { return 'Cari pelanggan ikut nama, kod, telefon atau emel.'; }

    public function rules(): array { return ['kata' => 'required|string|min:2|max:80']; }

    public function permission(): string { return 'crm.view'; }

    public function handle(array $p): array
    {
        $k = '%'.addcslashes($p['kata'], '%_').'%';
        $rows = Customer::where(fn ($q) => $q->where('name', 'like', $k)->orWhere('customer_code', 'like', $k)
            ->orWhere('contact_phone', 'like', $k)->orWhere('contact_email', 'like', $k))->limit(10)->get(['id', 'customer_code', 'name', 'contact_person', 'contact_phone', 'contact_email']);

        return ['bilangan' => $rows->count(), 'senarai' => $rows->toArray()];
    }
}
