<?php

namespace App\Modules\Vision\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Daftar kamera (BAB 56C Fasa 1).
 *
 * URL RTSP disimpan berenkripsi dan TIDAK pernah dipulangkan ke klien.
 */
class CctvCamera extends Model
{
    use BelongsToTenant;

    protected $table = 'cctv_cameras';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'location',
        'rtsp_url_encrypted',
        'status',
        'record_events',
        'last_seen_at',
    ];

    protected $hidden = ['rtsp_url_encrypted'];

    protected $casts = [
        'record_events' => 'boolean',
        'last_seen_at' => 'datetime',
        'branch_id' => 'integer',
    ];

    public function events()
    {
        return $this->hasMany(CctvEvent::class, 'camera_id');
    }

    public function retention()
    {
        return $this->hasOne(CctvRetention::class, 'camera_id');
    }

    /** Simpan URL RTSP berenkripsi. */
    public function setRtsp(?string $url): void
    {
        $this->rtsp_url_encrypted = $url ? Crypt::encryptString($url) : null;
    }

    /** Baca URL RTSP (hanya untuk servis kamera, bukan untuk klien). */
    public function rtsp(): ?string
    {
        return $this->rtsp_url_encrypted ? Crypt::decryptString($this->rtsp_url_encrypted) : null;
    }

    /** Paparan selamat untuk API — tiada URL sebenar. */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'branch_id' => $this->branch_id,
            'status' => $this->status,
            'record_events' => $this->record_events,
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
            'has_stream' => filled($this->rtsp_url_encrypted),
        ];
    }
}
