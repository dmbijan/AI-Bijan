<?php

namespace App\Modules\VideoStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Brand kit video — padanan `aibijan-brand.json`.
 *
 * Satu fail dipakai oleh ketiga-tiga skill (mograph, motion, video editor).
 */
class VideoBrandKit extends Model
{
    use BelongsToTenant;

    protected $table = 'video_brand_kits';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'brand',
        'handle',
        'cta',
        'color_primary',
        'color_accent',
        'color_background',
        'font',
        'logo_path',
        'is_default',
        'schema_version',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'schema_version' => 'integer',
    ];

    protected $attributes = [
        'is_default' => false,
        'schema_version' => 1,
    ];

    /**
     * Bentuk `aibijan-brand.json` yang dibaca oleh skill.
     */
    public function keJson(): array
    {
        return [
            'version' => $this->schema_version,
            'brand' => $this->brand,
            'handle' => $this->handle,
            'cta' => $this->cta,
            'colors' => [
                'primary' => $this->color_primary,
                'accent' => $this->color_accent,
                'background' => $this->color_background,
            ],
            'font' => $this->font,
            'logo' => $this->logo_path,
        ];
    }

    /**
     * Font yang disokong skill.
     *
     * @return array<int, string>
     */
    public static function senaraiFont(): array
    {
        return ['Plus Jakarta Sans', 'DM Sans', 'Space Grotesk', 'Instrument Serif'];
    }
}
