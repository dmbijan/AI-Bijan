<?php

namespace App\Modules\ContentStudio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContentStudio\Models\ContentBrandProfile;
use App\Modules\ContentStudio\Models\ContentChat;
use App\Modules\ContentStudio\Models\ContentComment;
use App\Modules\ContentStudio\Models\ContentIdea;
use App\Modules\ContentStudio\Models\ContentModerationRule;
use App\Modules\ContentStudio\Models\ContentPiece;
use App\Modules\ContentStudio\Models\ContentPlatform;
use App\Modules\ContentStudio\Services\ContentStudioService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentStudioController extends Controller
{
    public function __construct(private ContentStudioService $studio) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    // ---------------------------------------------------------------
    // Platform
    // ---------------------------------------------------------------

    public function platforms(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ContentPlatform::query()
                ->where('tenant_id', $this->tenantId())
                ->orderBy('platform')
                ->get()
                ->each(function ($p) {
                    $p->makeHidden(['credentials']);
                }),
        ]);
    }

    public function simpanPlatform(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'in:facebook,instagram,tiktok,linkedin,x,whatsapp,youtube,telegram,google_business'],
            'display_name' => ['nullable', 'string', 'max:120'],
            'account_id' => ['nullable', 'string', 'max:180'],
            'account_name' => ['nullable', 'string', 'max:180'],
            'credentials' => ['nullable', 'array'],
            'capabilities' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'auto_publish' => ['nullable', 'boolean'],
            'auto_reply' => ['nullable', 'boolean'],
            'max_daily_posts' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $platform = ContentPlatform::updateOrCreate(
            [
                'tenant_id' => $this->tenantId(),
                'platform' => $validated['platform'],
                'account_id' => $validated['account_id'] ?? 'default',
            ],
            array_merge($validated, ['tenant_id' => $this->tenantId()])
        );

        $platform->makeHidden(['credentials']);

        return response()->json(['success' => true, 'data' => $platform], 201);
    }

    // ---------------------------------------------------------------
    // Brand voice
    // ---------------------------------------------------------------

    public function brandProfiles(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ContentBrandProfile::query()
                ->where('tenant_id', $this->tenantId())
                ->orderByDesc('is_default')
                ->get(),
        ]);
    }

    public function simpanBrandProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'tone' => ['nullable', 'string', 'max:1000'],
            'persona' => ['nullable', 'string', 'max:1000'],
            'do_and_dont' => ['nullable', 'string', 'max:2000'],
            'key_phrases' => ['nullable', 'array'],
            'banned_words' => ['nullable', 'array'],
            'language' => ['nullable', 'string', 'max:10'],
            'default_cta' => ['nullable', 'string', 'max:255'],
            'postings_per_week' => ['nullable', 'integer', 'min:0', 'max:50'],
            'is_default' => ['nullable', 'boolean'],
            'sample_posts' => ['nullable', 'array'],
        ]);

        $profile = ContentBrandProfile::updateOrCreate(
            ['tenant_id' => $this->tenantId(), 'name' => $validated['name']],
            array_merge($validated, ['tenant_id' => $this->tenantId()])
        );

        return response()->json(['success' => true, 'data' => $profile], 201);
    }

    // ---------------------------------------------------------------
    // Kandungan
    // ---------------------------------------------------------------

    public function index(Request $request): JsonResponse
    {
        $query = ContentPiece::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('content_type')) {
            $query->where('content_type', $request->input('content_type'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    /**
     * Jana kandungan dengan AI.
     */
    public function jana(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => ['nullable', 'string', 'max:500'],
            'title' => ['nullable', 'string', 'max:180'],
            'brief' => ['nullable', 'string', 'max:2000'],
            'audience' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:60'],
            'keywords' => ['nullable', 'array'],
            'hashtags' => ['nullable', 'array'],
            'content_type' => ['nullable', 'in:post,story,reel,video,carousel,article,thread'],
            'tone' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:10'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'media_assets' => ['nullable', 'array'],
            'idea_id' => ['nullable', 'integer'],
        ]);

        if (empty($validated['topic']) && empty($validated['title']) && empty($validated['brief'])) {
            return response()->json([
                'success' => false,
                'message' => 'Sertakan sekurang-kurangnya topic, title atau brief',
            ], 422);
        }

        $piece = $this->studio->janaKandungan($this->tenantId(), $validated);

        return response()->json(['success' => true, 'data' => $piece], 201);
    }

    /**
     * Simpan / kemas kini kandungan secara manual.
     */
    public function simpan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['nullable', 'integer'],
            'title' => ['required_without:id', 'string', 'max:180'],
            'body' => ['nullable', 'string', 'max:20000'],
            'hook' => ['nullable', 'string', 'max:1000'],
            'cta' => ['nullable', 'string', 'max:1000'],
            'content_type' => ['nullable', 'in:post,story,reel,video,carousel,article,thread'],
            'status' => ['nullable', 'in:idea,draft,in_review,approved,scheduled,published,archived'],
            'brand_profile_id' => ['nullable', 'integer'],
            'media_assets' => ['nullable', 'array'],
            'hashtags' => ['nullable', 'array'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        if (! empty($validated['id'])) {
            $piece = ContentPiece::query()
                ->where('tenant_id', $this->tenantId())
                ->findOrFail($validated['id']);

            $piece->update($validated);
        } else {
            $validated['tenant_id'] = $this->tenantId();
            $validated['created_by'] = auth()->id();
            $piece = ContentPiece::create($validated);
        }

        return response()->json(['success' => true, 'data' => $piece->fresh()], 201);
    }

    /**
     * Jadwal penerbitan ke platform.
     */
    public function jadual(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'platform_ids' => ['required', 'array', 'min:1'],
            'platform_ids.*' => ['integer'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $publications = $this->studio->jadualTerbit(
            $this->tenantId(),
            $id,
            $validated['platform_ids'],
            $validated['scheduled_at'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => $publications,
            'message' => count($publications) . ' penerbitan dijadualkan',
        ], 201);
    }

    /**
     * Preview adaptasi untuk platform.
     */
    public function preview(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'max:60'],
        ]);

        $piece = ContentPiece::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->studio->adaptasiUntukPlatform($piece, $validated['platform']),
        ]);
    }

    /**
     * Terbitkan queued (scheduler).
     */
    public function terbitkan(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->integer('limit', 20), 1), 100);

        return response()->json([
            'success' => true,
            'data' => $this->studio->terbitQueued($this->tenantId(), $limit),
        ]);
    }

    // ---------------------------------------------------------------
    // Komen
    // ---------------------------------------------------------------

    public function komen(Request $request): JsonResponse
    {
        $query = ContentComment::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('posted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('sentiment')) {
            $query->where('sentiment', $request->input('sentiment'));
        }

        if ($request->filled('platform_id')) {
            $query->where('content_platform_id', $request->input('platform_id'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    /**
     * Ingest komen (webhook platform).
     */
    public function ingestKomen(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform_id' => ['required', 'integer'],
            'external_id' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:5000'],
            'author_name' => ['nullable', 'string', 'max:180'],
            'author_id' => ['nullable', 'string', 'max:180'],
            'author_avatar' => ['nullable', 'url', 'max:500'],
            'platform_post_id' => ['nullable', 'string', 'max:180'],
            'content_piece_id' => ['nullable', 'integer'],
            'like_count' => ['nullable', 'integer', 'min:0'],
        ]);

        $komen = $this->studio->ingestKomen($this->tenantId(), $validated['platform_id'], $validated);

        return response()->json(['success' => true, 'data' => $komen], 201);
    }

    /**
     * Jana cadangan balasan AI.
     */
    public function janaBalasan(Request $request, int $id): JsonResponse
    {
        $komen = ContentComment::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->studio->janaBalasanAI($this->tenantId(), $komen),
        ]);
    }

    /**
     * Balas komen.
     */
    public function balasKomen(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:2000'],
            'from_ai' => ['nullable', 'boolean'],
        ]);

        $komen = $this->studio->balasKomen(
            $this->tenantId(),
            $id,
            $validated['reply'],
            $validated['from_ai'] ?? false
        );

        return response()->json(['success' => true, 'data' => $komen]);
    }

    /**
     * Sembunyikan komen.
     */
    public function sembunyiKomen(int $id): JsonResponse
    {
        $komen = $this->studio->sembunyiKomen($this->tenantId(), $id);

        return response()->json(['success' => true, 'data' => $komen]);
    }

    // ---------------------------------------------------------------
    // Chat
    // ---------------------------------------------------------------

    public function chats(Request $request): JsonResponse
    {
        $query = ContentChat::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('last_message_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->boolean('requires_human')) {
            $query->where('requires_human', true);
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    public function ingestChat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform_id' => ['required', 'integer'],
            'external_conversation_id' => ['nullable', 'string', 'max:180'],
            'customer_name' => ['nullable', 'string', 'max:180'],
            'customer_id' => ['nullable', 'string', 'max:180'],
            'customer_avatar' => ['nullable', 'url', 'max:500'],
            'body' => ['required', 'string', 'max:5000'],
            'external_id' => ['nullable', 'string', 'max:180'],
        ]);

        $chat = $this->studio->ingestChat($this->tenantId(), $validated['platform_id'], $validated);

        return response()->json(['success' => true, 'data' => $chat], 201);
    }

    public function balasChat(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:4000'],
            'from_ai' => ['nullable', 'boolean'],
        ]);

        $chat = $this->studio->balasChat(
            $this->tenantId(),
            $id,
            $validated['reply'],
            $validated['from_ai'] ?? false
        );

        return response()->json(['success' => true, 'data' => $chat]);
    }

    // ---------------------------------------------------------------
    // Moderation
    // ---------------------------------------------------------------

    public function moderationRules(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ContentModerationRule::query()
                ->where('tenant_id', $this->tenantId())
                ->orderBy('type')
                ->get(),
        ]);
    }

    public function simpanModerationRule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:keyword,toxicity,spam,regex,blocklist'],
            'pattern' => ['nullable', 'string', 'max:1000'],
            'action' => ['required', 'in:hide,flag,block,auto_reply,escalate'],
            'severity' => ['nullable', 'in:low,medium,high'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $rule = ContentModerationRule::create(array_merge($validated, [
            'tenant_id' => $this->tenantId(),
        ]));

        return response()->json(['success' => true, 'data' => $rule], 201);
    }

    // ---------------------------------------------------------------
    // Idea
    // ---------------------------------------------------------------

    public function idea(Request $request): JsonResponse
    {
        $query = ContentIdea::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('score');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    public function simpanIdea(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'in:manual,ai,research,competitor,customer'],
            'category' => ['nullable', 'string', 'max:120'],
            'keywords' => ['nullable', 'array'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $idea = ContentIdea::create(array_merge($validated, [
            'tenant_id' => $this->tenantId(),
        ]));

        return response()->json(['success' => true, 'data' => $idea], 201);
    }

    // ---------------------------------------------------------------
    // Ringkasan & prestasi
    // ---------------------------------------------------------------

    public function ringkasan(Request $request): JsonResponse
    {
        $hari = min(max((int) $request->integer('days', 30), 1), 365);

        return response()->json([
            'success' => true,
            'data' => $this->studio->ringkasan($this->tenantId(), $hari),
        ]);
    }

    public function prestasi(Request $request): JsonResponse
    {
        $hari = min(max((int) $request->integer('days', 30), 1), 365);

        return response()->json([
            'success' => true,
            'data' => $this->studio->prestasiPlatform($this->tenantId(), $hari),
        ]);
    }
}
