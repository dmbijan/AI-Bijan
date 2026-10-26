<?php

namespace App\Modules\Community\Services;

use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunityReply;
use App\Modules\Community\Models\CommunitySpace;
use Illuminate\Support\Str;

/**
 * CommunityService — komuniti AI BIJAN (forum, showcase, academy).
 *
 * ✅ Idea #29 (BAB 86): "buatkan satu community AI BIJAN (ada idea?)".
 *
 * Idea komuniti:
 * - **Showcase** — tenant tunjuk hasil kerja (dengan kebenaran) → inspirasi
 * - **Academy** — tutorial AI BIJAN, resipi automasi
 * - **Help** — soal jawab; jawapan diterima jadi artikel Knowledge Base
 * - **Idea** — cadangan ciri; undi terbanyak masuk backlog
 * - Semua diskop: `public` (SEO funnel), `members` (tenant AI BIJAN), `private`
 */
class CommunityService
{
    /**
     * Cipta ruang komuniti.
     */
    public function ciptaRuang(array $data): CommunitySpace
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);

        // Pastikan slug unik — DALAM tenant semasa sahaja (skop global aktif)
        $asas = $slug;
        $n = 1;
        while (CommunitySpace::where('slug', $slug)->exists()) {
            $slug = $asas . '-' . $n++;
        }

        // `tenant_id` TIDAK dihantar — trait BelongsToTenant mengisinya dari
        // TenantContext. Menghantar nilai sendiri akan ditolak jika berbeza.
        return CommunitySpace::create(array_merge([
            'slug' => $slug,
            'name' => $data['name'] ?? 'Ruang Baharu',
            'space_type' => CommunitySpace::TYPE_FORUM,
            'visibility' => CommunitySpace::VISIBILITY_PUBLIC,
            'is_active' => true,
        ], $data, ['slug' => $slug]));
    }

    /**
     * Cipta pos.
     *
     * ⚠️ 27 Okt 2026: parameter `$tenantId` DIBUANG. Nilai itu datang dari
     * pemanggil (boleh null) → `tenant_id` boleh jadi NULL atau salah tenant.
     * Trait BelongsToTenant mengisi `tenant_id` dari TenantContext.
     * `CommunitySpace::findOrFail()` kini DITAPIS tenant → ruang tenant lain 404.
     */
    public function ciptaPos(int $spaceId, array $data, ?int $tenantId = null): CommunityPost
    {
        $ruang = CommunitySpace::findOrFail($spaceId);   // skop tenant aktif

        $pos = CommunityPost::create([
            'community_space_id' => $ruang->id,
            'author_user_id' => auth()->id(),
            'post_type' => $data['post_type'] ?? CommunityPost::TYPE_DISCUSSION,
            'title' => $data['title'] ?? 'Tanpa Tajuk',
            'body' => $data['body'] ?? null,
            'attachments' => $data['attachments'] ?? null,
            'tags' => $data['tags'] ?? null,
            'status' => CommunityPost::STATUS_PUBLISHED,
        ]);

        $ruang->increment('post_count');

        return $pos;
    }

    /**
     * Balas pos. Laluan `findOrFail()` DITAPIS tenant.
     */
    public function balas(int $postId, string $body, ?int $tenantId = null): CommunityReply
    {
        $pos = CommunityPost::findOrFail($postId);   // skop tenant aktif

        if (! $pos->tersiar()) {
            throw new \RuntimeException('Pos ini tidak menerima balasan');
        }

        $balasan = CommunityReply::create([
            'community_post_id' => $pos->id,
            'author_user_id' => auth()->id(),
            'body' => $body,
            'status' => 'published',
        ]);

        $pos->increment('reply_count');

        return $balasan;
    }

    /**
     * Tandakan balasan sebagai jawapan diterima.
     */
    public function terimaBalasan(int $postId, int $replyId): CommunityPost
    {
        $pos = CommunityPost::findOrFail($postId);

        CommunityReply::where('community_post_id', $pos->id)->update(['is_accepted' => false]);

        $balasan = CommunityReply::where('community_post_id', $pos->id)->findOrFail($replyId);
        $balasan->update(['is_accepted' => true]);

        $pos->update([
            'is_solved' => true,
            'accepted_reply_id' => $balasan->id,
        ]);

        return $pos->fresh();
    }

    /**
     * Senarai ruang yang boleh dilihat.
     */
    public function ruangBolehLihat(bool $ahli = false): array
    {
        $query = CommunitySpace::query()->where('is_active', true);

        if (! $ahli) {
            $query->where('visibility', CommunitySpace::VISIBILITY_PUBLIC);
        }

        return $query->orderByDesc('member_count')->get()->toArray();
    }

    /**
     * Feed — pos terkini.
     */
    public function feed(?int $spaceId = null, string $susun = 'terbaru', int $limit = 25): array
    {
        $query = CommunityPost::query()
            ->where('status', CommunityPost::STATUS_PUBLISHED);

        if ($spaceId) {
            $query->where('community_space_id', $spaceId);
        }

        $susun === 'popular'
            ? $query->orderByDesc('like_count')->orderByDesc('reply_count')
            : $query->orderByDesc('is_pinned')->orderByDesc('created_at');

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * Ringkasan komuniti.
     */
    public function ringkasan(): array
    {
        $ruang = CommunitySpace::query()->where('is_active', true)->get();
        $pos = CommunityPost::query()->where('status', CommunityPost::STATUS_PUBLISHED);

        return [
            'jumlah_ruang' => $ruang->count(),
            'ikut_jenis' => $ruang->groupBy('space_type')->map(fn ($g) => $g->count())->toArray(),
            'jumlah_pos' => (clone $pos)->count(),
            'jumlah_selesai' => (clone $pos)->where('is_solved', true)->count(),
            'jumlah_balasan' => CommunityReply::query()->where('status', 'published')->count(),
            'ruang_awam' => $ruang->where('visibility', CommunitySpace::VISIBILITY_PUBLIC)->count(),
        ];
    }
}
