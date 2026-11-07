<?php

namespace App\Modules\OpenGenAI\Services;

use App\Modules\OpenGenAI\Models\GenAiProvider;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ProviderAdapter — dispatch permintaan generatif mengikut jenis provider.
 *
 * Reka bentuk: semua provider dikongsi satu kontrak `hantar()` yang
 * menerima prompt + parameter dan memulirkan array seragam:
 *
 *   ['success' => bool, 'text' => ?string, 'url' => ?string,
 *    'meta' => array, 'error' => ?string]
 *
 * Ini membolehkan AI BIJAN menambah provider baharu tanpa menyentuh
 * kod pemanggil — inti idea "open generative AI".
 */
class ProviderAdapter
{
    /**
     * Hantar permintaan generatif kepada provider.
     *
     * @param  array<string, mixed>  $parameter
     * @return array{success: bool, text: ?string, url: ?string, meta: array, error: ?string}
     */
    public function hantar(GenAiProvider $provider, string $prompt, array $parameter = []): array
    {
        try {
            return match ($provider->kind) {
                GenAiProvider::KIND_ANTHROPIC => $this->anthropic($provider, $prompt, $parameter),
                GenAiProvider::KIND_GEMINI => $this->gemini($provider, $prompt, $parameter),
                GenAiProvider::KIND_GROQ => $this->openaiCompatible($provider, $prompt, $parameter),
                GenAiProvider::KIND_OLLAMA => $this->ollama($provider, $prompt, $parameter),
                GenAiProvider::KIND_REPLICATE => $this->replicate($provider, $prompt, $parameter),
                GenAiProvider::KIND_OPENAI,
                GenAiProvider::KIND_CUSTOM => $this->openaiCompatible($provider, $prompt, $parameter),
                default => $this->openaiCompatible($provider, $prompt, $parameter),
            };
        } catch (Throwable $e) {
            Log::warning('OpenGenAI: provider gagal', [
                'provider_id' => $provider->id,
                'kind' => $provider->kind,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'text' => null,
                'url' => null,
                'meta' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Kontrak OpenAI `/chat/completions` — digunakan oleh OpenAI, Groq,
     * Together, LM Studio dan kebanyakan pelayan serasi.
     */
    protected function openaiCompatible(GenAiProvider $provider, string $prompt, array $parameter): array
    {
        $base = rtrim($provider->base_url ?: 'https://api.openai.com/v1', '/');
        $model = $parameter['model'] ?? $provider->default_model ?? 'gpt-4o-mini';

        $body = [
            'model' => $model,
            'messages' => $this->binaPesan($prompt, $parameter),
            'max_tokens' => (int) ($parameter['max_tokens'] ?? 1024),
            'temperature' => (float) ($parameter['temperature'] ?? 0.7),
        ];

        if (! empty($parameter['response_format'])) {
            $body['response_format'] = $parameter['response_format'];
        }

        $response = $this->hantarHttp($provider, $base.'/chat/completions', $body);

        if (! $response->successful()) {
            return $this->ralat($response);
        }

        $data = $response->json();
        $teks = $data['choices'][0]['message']['content'] ?? null;

        return [
            'success' => true,
            'text' => $teks,
            'url' => null,
            'meta' => [
                'model' => $data['model'] ?? $model,
                'usage' => $data['usage'] ?? [],
                'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
            ],
            'error' => null,
        ];
    }

    /**
     * Kontrak Anthropic `/v1/messages`.
     */
    protected function anthropic(GenAiProvider $provider, string $prompt, array $parameter): array
    {
        $base = rtrim($provider->base_url ?: 'https://api.anthropic.com/v1', '/');
        $model = $parameter['model'] ?? $provider->default_model ?? 'claude-sonnet-4-5';

        $body = [
            'model' => $model,
            'max_tokens' => (int) ($parameter['max_tokens'] ?? 1024),
            'temperature' => (float) ($parameter['temperature'] ?? 0.7),
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ];

        if (! empty($parameter['system_prompt'])) {
            $body['system'] = $parameter['system_prompt'];
        }

        $response = $this->hantarHttp($provider, $base.'/messages', $body, [
            'x-api-key' => $provider->api_key,
            'anthropic-version' => '2023-06-01',
        ]);

        if (! $response->successful()) {
            return $this->ralat($response);
        }

        $data = $response->json();
        $teks = collect($data['content'] ?? [])
            ->filter(fn ($b) => ($b['type'] ?? '') === 'text')
            ->map(fn ($b) => $b['text'] ?? '')
            ->implode("\n");

        return [
            'success' => true,
            'text' => $teks !== '' ? $teks : null,
            'url' => null,
            'meta' => [
                'model' => $data['model'] ?? $model,
                'usage' => $data['usage'] ?? [],
                'stop_reason' => $data['stop_reason'] ?? null,
            ],
            'error' => null,
        ];
    }

    /**
     * Kontrak Gemini `generateContent`.
     */
    protected function gemini(GenAiProvider $provider, string $prompt, array $parameter): array
    {
        $base = rtrim($provider->base_url ?: 'https://generativelanguage.googleapis.com/v1beta', '/');
        $model = $parameter['model'] ?? $provider->default_model ?? 'gemini-2.0-flash';

        $body = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'maxOutputTokens' => (int) ($parameter['max_tokens'] ?? 1024),
                'temperature' => (float) ($parameter['temperature'] ?? 0.7),
            ],
        ];

        if (! empty($parameter['system_prompt'])) {
            $body['systemInstruction'] = ['parts' => [['text' => $parameter['system_prompt']]]];
        }

        $response = $this->hantarHttp($provider, $base.'/models/'.$model.':generateContent', $body);

        if (! $response->successful()) {
            return $this->ralat($response);
        }

        $data = $response->json();
        $teks = collect($data['candidates'][0]['content']['parts'] ?? [])
            ->map(fn ($p) => $p['text'] ?? '')
            ->implode("\n");

        return [
            'success' => true,
            'text' => $teks !== '' ? $teks : null,
            'url' => null,
            'meta' => [
                'model' => $model,
                'usage' => $data['usageMetadata'] ?? [],
                'finish_reason' => $data['candidates'][0]['finishReason'] ?? null,
            ],
            'error' => null,
        ];
    }

    /**
     * Ollama tempatan — `http://localhost:11434`.
     */
    protected function ollama(GenAiProvider $provider, string $prompt, array $parameter): array
    {
        $base = rtrim($provider->base_url ?: 'http://localhost:11434', '/');
        $model = $parameter['model'] ?? $provider->default_model ?? 'llama3.2';

        $body = [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => (float) ($parameter['temperature'] ?? 0.7),
                'num_predict' => (int) ($parameter['max_tokens'] ?? 1024),
            ],
        ];

        $response = $this->hantarHttp($provider, $base.'/api/generate', $body);

        if (! $response->successful()) {
            return $this->ralat($response);
        }

        $data = $response->json();

        return [
            'success' => true,
            'text' => $data['response'] ?? null,
            'url' => null,
            'meta' => [
                'model' => $data['model'] ?? $model,
                'usage' => [
                    'prompt_tokens' => $data['prompt_eval_count'] ?? 0,
                    'completion_tokens' => $data['eval_count'] ?? 0,
                ],
                'done_reason' => $data['done_reason'] ?? null,
            ],
            'error' => null,
        ];
    }

    /**
     * Replicate — model boleh menerima prompt dan memulirkan URL output.
     */
    protected function replicate(GenAiProvider $provider, string $prompt, array $parameter): array
    {
        $base = rtrim($provider->base_url ?: 'https://api.replicate.com/v1', '/');
        $model = $parameter['model'] ?? $provider->default_model;

        if (! $model) {
            return [
                'success' => false,
                'text' => null,
                'url' => null,
                'meta' => [],
                'error' => 'Model Replicate tidak ditetapkan.',
            ];
        }

        $body = ['input' => array_merge(['prompt' => $prompt], $parameter['input'] ?? [])];

        $response = $this->hantarHttp($provider, $base.'/models/'.$model.'/predictions', $body, [
            'Prefer' => 'wait',
        ]);

        if (! $response->successful()) {
            return $this->ralat($response);
        }

        $data = $response->json();
        $output = $data['output'] ?? null;
        $url = is_array($output) ? ($output[0] ?? null) : $output;

        return [
            'success' => ($data['status'] ?? '') === 'succeeded',
            'text' => is_string($output) ? $output : null,
            'url' => is_string($url) ? $url : null,
            'meta' => [
                'model' => $model,
                'prediction_id' => $data['id'] ?? null,
                'status' => $data['status'] ?? null,
                'metrics' => $data['metrics'] ?? [],
            ],
            'error' => ($data['status'] ?? '') === 'succeeded' ? null : ($data['error'] ?? 'Prediksi gagal.'),
        ];
    }

    /**
     * Bina array mesej mengikut kontrak OpenAI.
     *
     * @param  array<string, mixed>  $parameter
     * @return array<int, array{role: string, content: string}>
     */
    protected function binaPesan(string $prompt, array $parameter): array
    {
        $mesej = [];

        if (! empty($parameter['system_prompt'])) {
            $mesej[] = ['role' => 'system', 'content' => (string) $parameter['system_prompt']];
        }

        $mesej[] = ['role' => 'user', 'content' => $prompt];

        return $mesej;
    }

    /**
     * Hantar permintaan HTTP dengan header provider yang betul.
     *
     * @param  array<string, mixed>  $body
     * @param  array<string, string>  $headerTambahan
     */
    protected function hantarHttp(GenAiProvider $provider, string $url, array $body, array $headerTambahan = []): Response
    {
        $header = array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ], $headerTambahan);

        // Bearer digunakan sebagai fallback; provider tertentu guna header sendiri.
        if (! isset($header['x-api-key']) && ! isset($header['Authorization'])) {
            $header['Authorization'] = 'Bearer '.$provider->kunciApi();
        }

        $timeout = (int) config('open_gen_ai.timeout_seconds', 120);

        return Http::withHeaders($header)
            ->timeout($timeout)
            ->withOptions(['query' => $this->parameterUrl($provider)])
            ->post($url, $body);
    }

    /**
     * Parameter URL mengikut provider (Gemini guna key query).
     *
     * @return array<string, string>
     */
    protected function parameterUrl(GenAiProvider $provider): array
    {
        if ($provider->kind === GenAiProvider::KIND_GEMINI) {
            $kunci = $provider->kunciApi();

            if ($kunci !== '') {
                return ['key' => $kunci];
            }
        }

        return [];
    }

    /**
     * Ubah respons HTTP gagal kepada bentuk seragam.
     *
     * @return array{success: bool, text: ?string, url: ?string, meta: array, error: ?string}
     */
    protected function ralat(Response $response): array
    {
        $badan = $response->json();

        $mesej = $badan['error']['message']
            ?? $badan['message']
            ?? $badan['detail']
            ?? ('Provider_HTTP_'.$response->status());

        Log::warning('OpenGenAI: provider HTTP gagal', [
            'status' => $response->status(),
            'error' => $mesej,
        ]);

        return [
            'success' => false,
            'text' => null,
            'url' => null,
            'meta' => ['status' => $response->status()],
            'error' => (string) $mesej,
        ];
    }
}
