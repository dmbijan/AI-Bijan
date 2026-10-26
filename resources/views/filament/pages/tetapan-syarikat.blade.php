<x-filament-panels::page>
    @php($ringkasan = $this->ringkasan())
    @php($plan = $ringkasan['plan'] ?? null)
    @php($langganan = $ringkasan['langganan'] ?? null)

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- ============ PROFIL SYARIKAT ============ --}}
        <div class="lg:col-span-2">
            <form wire:submit="simpan">
                {{ $this->form }}

                @if ($this->bolehUbah())
                    <div class="mt-4">
                        <x-filament::button type="submit">
                            Simpan Perubahan
                        </x-filament::button>
                    </div>
                @else
                    <x-filament::section class="mt-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Anda hanya boleh melihat tetapan ini. Hubungi pemilik syarikat
                            untuk membuat perubahan.
                        </p>
                    </x-filament::section>
                @endif
            </form>
        </div>

        {{-- ============ LANGGANAN ============ --}}
        <div class="space-y-6">
            <x-filament::section heading="Langganan">
                @if ($plan)
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Plan</span>
                            <x-filament::badge color="warning">
                                {{ $plan['name'] }}
                            </x-filament::badge>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Harga</span>
                            <span class="font-semibold">
                                RM {{ number_format($plan['price_monthly'], 2) }} / bulan
                            </span>
                        </div>

                        @if ($langganan)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                                <x-filament::badge :color="$langganan['aktif'] ? 'success' : 'danger'">
                                    {{ $langganan['status'] }}
                                </x-filament::badge>
                            </div>
                        @endif

                        <hr class="border-gray-200 dark:border-white/10">

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Had Pengguna</span>
                            <span>{{ $plan['max_users'] ?? 'Tanpa had' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Had Lokasi</span>
                            <span>{{ $plan['max_locations'] ?? 'Tanpa had' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Kredit AI</span>
                            <span>{{ number_format($plan['ai_credits_monthly']) }} / bulan</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Modul Aktif</span>
                            <span class="font-semibold">{{ $ringkasan['jumlah_modul_aktif'] ?? 0 }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Tiada plan aktif. Hubungi pentadbir platform.
                    </p>
                @endif
            </x-filament::section>

            {{-- ============ MODUL AKTIF ============ --}}
            <x-filament::section heading="Modul Aktif">
                @php($modul = $ringkasan['modul_aktif'] ?? [])
                @if ($modul === [])
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Tiada modul diaktifkan.
                    </p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach ($modul as $m)
                            <x-filament::badge color="gray">
                                {{ $m }}
                            </x-filament::badge>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>
    </div>

    {{-- ============ PENGGUNA ============ --}}
    <x-filament::section heading="Pengguna Syarikat" class="mt-6">
        @php($senaraiPengguna = $this->pengguna())

        @if ($senaraiPengguna === [])
            <p class="text-sm text-gray-500 dark:text-gray-400">Tiada pengguna.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="py-2 text-start font-medium text-gray-500 dark:text-gray-400">Nama</th>
                            <th class="py-2 text-start font-medium text-gray-500 dark:text-gray-400">E-mel</th>
                            <th class="py-2 text-start font-medium text-gray-500 dark:text-gray-400">Peranan</th>
                            <th class="py-2 text-start font-medium text-gray-500 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($senaraiPengguna as $p)
                            <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                                <td class="py-2 font-medium">{{ $p['nama'] }}</td>
                                <td class="py-2 text-gray-600 dark:text-gray-400">{{ $p['emel'] }}</td>
                                <td class="py-2">{{ $p['peranan'] }}</td>
                                <td class="py-2">
                                    <x-filament::badge :color="$p['status'] === 'active' ? 'success' : 'gray'">
                                        {{ $p['status'] }}
                                    </x-filament::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
