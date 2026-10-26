<?php

namespace App\Modules\SignDocs\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'sign_docs_templates';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'description',
        'category',
        'content',
        'placeholders',
        'file_path',
        'file_type',
        'thumbnail_path',
        'is_active',
        'usage_count',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'template_id');
    }

    // Accessors
    public function getPlaceholderListAttribute()
    {
        return collect($this->placeholders)->pluck('name')->toArray();
    }

    // Scopes
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function incrementUsageCount()
    {
        $this->increment('usage_count');
    }

    public function hasPlaceholder($name)
    {
        return collect($this->placeholders)->contains('name', $name);
    }

    public function replacePlaceholders($content, $data)
    {
        foreach ($this->placeholders as $placeholder) {
            $key = $placeholder['prefix'] . $placeholder['name'] . $placeholder['suffix'];
            $value = $data[$placeholder['name']] ?? $placeholder['default'] ?? '';
            $content = str_replace($key, $value, $content);
        }
        return $content;
    }
}
