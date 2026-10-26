<?php

namespace App\Modules\ContentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentBrandProfile extends Model
{
    use BelongsToTenant;

    protected $table = 'content_brand_profiles';

    protected $fillable = [
        'tenant_id',
        'name',
        'tone',
        'persona',
        'do_and_dont',
        'key_phrases',
        'banned_words',
        'language',
        'default_cta',
        'postings_per_week',
        'is_default',
        'sample_posts',
    ];

    protected $casts = [
        'key_phrases' => 'array',
        'banned_words' => 'array',
        'sample_posts' => 'array',
        'is_default' => 'boolean',
        'postings_per_week' => 'integer',
    ];

    protected $attributes = [
        'name' => 'Default',
        'language' => 'ms-MY',
        'postings_per_week' => 3,
        'is_default' => false,
    ];

    /**
     * Bina arahan sistem untuk AI mengikut brand voice.
     */
    public function systemPrompt(): string
    {
        $baris = ["Anda penulis kandungan untuk {$this->name}."];

        if ($this->tone) {
            $baris[] = "Nada: {$this->tone}";
        }

        if ($this->persona) {
            $baris[] = "Persona: {$this->persona}";
        }

        if ($this->do_and_dont) {
            $baris[] = "Panduan: {$this->do_and_dont}";
        }

        if (! empty($this->key_phrases)) {
            $baris[] = 'Gunakan kata kunci: ' . implode(', ', $this->key_phrases);
        }

        if (! empty($this->banned_words)) {
            $baris[] = 'JANGAN guna perkataan: ' . implode(', ', $this->banned_words);
        }

        if ($this->default_cta) {
            $baris[] = "Call-to-action lalai: {$this->default_cta}";
        }

        $baris[] = "Bahasa: {$this->language}";

        return implode("\n", $baris);
    }
}
