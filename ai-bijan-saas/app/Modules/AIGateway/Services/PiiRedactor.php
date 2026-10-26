<?php

namespace App\Modules\AIGateway\Services;

/**
 * PII Redaction (BAB 21.3)
 *
 * Membuang data sensitif sebelum prompt dihantar kepada provider luar.
 */
class PiiRedactor
{
    /**
     * Redact teks
     */
    public function redactText(string $text): string
    {
        if (!config('ai_gateway.redaction.enabled', true)) {
            return $text;
        }

        $patterns = config('ai_gateway.redaction.patterns', []);
        $replacement = config('ai_gateway.redaction.replacement', '[REDACTED]');

        foreach ($patterns as $name => $pattern) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        return $text;
    }

    /**
     * Redact struktur array secara rekursif
     */
    public function redact(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                $result[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $result[$key] = $this->redact($value);
            } elseif (is_string($value)) {
                $result[$key] = $this->redactText($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Redact mesej chat (role/content)
     */
    public function redactMessages(array $messages): array
    {
        return array_map(function ($message) {
            if (isset($message['content']) && is_string($message['content'])) {
                $message['content'] = $this->redactText($message['content']);
            }
            return $message;
        }, $messages);
    }

    protected function isSensitiveKey(string $key): bool
    {
        $sensitive = ['password', 'api_key', 'token', 'secret', 'credential', 'ssn', 'credit_card'];
        $lower = strtolower($key);

        foreach ($sensitive as $word) {
            if (str_contains($lower, $word)) {
                return true;
            }
        }

        return false;
    }
}
