<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder katalog model AI (BAB 21.2)
 *
 * Mengisi ai_model_catalog dengan model serveras.click.
 * Jalankan: php artisan db:seed --class=AiModelCatalogSeeder
 *
 * NOTA: Model ID dan harga adalah TARGET. Sahkan dengan dokumentasi
 * serveras.click semasa sebelum production.
 */
class AiModelCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('ai_model_catalog')) {
            $this->command?->error('Jadual ai_model_catalog tidak wujud. Jalankan migrate dahulu.');
            return;
        }

        $models = [
            [
                'provider_model_id' => 'asai/gpt-6-astra',
                'display_name' => 'GPT-6 Astra',
                'tier' => 'standard',
                'capabilities' => ['text', 'code', 'reasoning'],
                'max_context' => 128000,
                'input_rate' => 0.00015,
                'output_rate' => 0.00015,
            ],
            [
                'provider_model_id' => 'asai/claude-opus-4-8',
                'display_name' => 'Claude Opus 4.8',
                'tier' => 'premium',
                'capabilities' => ['text', 'code', 'reasoning'],
                'max_context' => 200000,
                'input_rate' => 0.0002,
                'output_rate' => 0.0002,
            ],
            [
                'provider_model_id' => 'asai/claude-opus-5',
                'display_name' => 'Claude Opus 5',
                'tier' => 'premium',
                'capabilities' => ['text', 'code', 'reasoning'],
                'max_context' => 200000,
                'input_rate' => 0.0002,
                'output_rate' => 0.0002,
            ],
            [
                'provider_model_id' => 'asai/claude-opus-5-5',
                'display_name' => 'Claude Opus 5.5',
                'tier' => 'ultra',
                'capabilities' => ['text', 'code', 'reasoning'],
                'max_context' => 400000,
                'input_rate' => 0.00025,
                'output_rate' => 0.00025,
            ],
            [
                'provider_model_id' => 'asai/kimi-k3',
                'display_name' => 'Kimi K3',
                'tier' => 'ultra',
                'capabilities' => ['text', 'long_context'],
                'max_context' => 200000,
                'input_rate' => 0.00025,
                'output_rate' => 0.00025,
            ],
            [
                'provider_model_id' => 'asai/qwen-3-8-max',
                'display_name' => 'Qwen 3.8 Max',
                'tier' => 'standard',
                'capabilities' => ['text', 'multilingual'],
                'max_context' => 128000,
                'input_rate' => 0.00015,
                'output_rate' => 0.00015,
            ],
            [
                'provider_model_id' => 'asai/deepseek-v4-pro',
                'display_name' => 'DeepSeek V4 Pro',
                'tier' => 'standard',
                'capabilities' => ['text', 'code'],
                'max_context' => 128000,
                'input_rate' => 0.00015,
                'output_rate' => 0.00015,
            ],
            [
                'provider_model_id' => 'asai/glm-5-3-flash',
                'display_name' => 'GLM 5.3 Flash',
                'tier' => 'flash',
                'capabilities' => ['text'],
                'max_context' => 32000,
                'input_rate' => 0.0001,
                'output_rate' => 0.0001,
            ],
            [
                'provider_model_id' => 'asai/minimax-m3',
                'display_name' => 'MiniMax M3',
                'tier' => 'flash',
                'capabilities' => ['text'],
                'max_context' => 64000,
                'input_rate' => 0.0001,
                'output_rate' => 0.0001,
            ],
            [
                'provider_model_id' => 'asai/grok-4-7',
                'display_name' => 'Grok 4.7',
                'tier' => 'standard',
                'capabilities' => ['text', 'realtime'],
                'max_context' => 128000,
                'input_rate' => 0.00015,
                'output_rate' => 0.00015,
            ],
            [
                'provider_model_id' => 'asai/muse-wonder-1-0',
                'display_name' => 'Muse Wonder 1.0',
                'tier' => 'standard',
                'capabilities' => ['text', 'creative'],
                'max_context' => 64000,
                'input_rate' => 0.00015,
                'output_rate' => 0.00015,
            ],
        ];

        $now = now();
        $count = 0;

        foreach ($models as $model) {
            DB::table('ai_model_catalog')->updateOrInsert(
                [
                    'provider_slug' => 'serveras',
                    'provider_model_id' => $model['provider_model_id'],
                ],
                [
                    'display_name' => $model['display_name'],
                    'tier' => $model['tier'],
                    'capabilities' => json_encode($model['capabilities']),
                    'max_context' => $model['max_context'],
                    'input_rate' => $model['input_rate'],
                    'output_rate' => $model['output_rate'],
                    'is_active' => true,
                    'availability_status' => 'available',
                    'last_verified' => now()->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $count++;
        }

        $this->command?->info("{$count} model AI dimasukkan ke katalog.");
        $this->command?->warn('PENTING: Sahkan ID model & harga dengan serveras.click sebelum production.');
    }
}
