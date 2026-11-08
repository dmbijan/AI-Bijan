<?php

namespace App\Modules\VideoStudio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\VideoStudio\Models\VideoBrandKit;
use App\Modules\VideoStudio\Models\VideoProject;
use App\Modules\VideoStudio\Services\VideoRenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VideoStudioController extends Controller
{
    public function __construct(private VideoRenderService $render) {}

    /**
     * Senarai projek video.
     */
    public function index(Request $request): JsonResponse
    {
        $query = VideoProject::query()->orderByDesc('created_at');

        if ($request->filled('skill')) {
            $query->where('skill', $request->input('skill'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 50))->get(),
            'meta' => [
                'skills' => VideoProject::senaraiSkill(),
                'gaya' => VideoProject::gayaMengikutSkill(),
            ],
        ]);
    }

    /**
     * Cipta projek video.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:200'],
            'skill' => ['nullable', 'string', 'in:mograph,motion,video-editor'],
            'topic' => ['nullable', 'string', 'max:300'],
            'title' => ['nullable', 'string', 'max:200'],
            'gaya' => ['nullable', 'string', 'max:40'],
            'aspect_ratio' => ['nullable', 'string', 'in:9:16,16:9,1:1,4:5'],
            'frame_rate' => ['nullable', 'integer', 'min:24', 'max:120'],
            'brand_kit_id' => ['nullable', 'integer', 'exists:video_brand_kits,id'],
            'voiceover' => ['nullable', 'file', 'mimes:mp3,wav,m4a,aac,ogg', 'max:'.((int) config('video_studio.limits.max_input_mb', 500) * 1024)],
            'script' => ['nullable', 'file', 'mimes:txt,md,srt,vtt', 'max:10240'],
            'source_video' => ['nullable', 'file', 'mimes:mp4,mov,webm,mkv', 'max:'.((int) config('video_studio.limits.max_input_mb', 500) * 1024)],
            'settings' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        foreach ([
            'voiceover' => 'voiceover_path',
            'script' => 'script_path',
            'source_video' => 'source_video_path',
        ] as $input => $medan) {
            if ($request->hasFile($input)) {
                $data[$medan] = $request->file($input)->store('video-studio/input', 'local');
            }

            unset($data[$input]);
        }

        $projek = $this->render->cipta($data);

        return response()->json([
            'success' => true,
            'message' => 'Projek video berjaya dicipta.',
            'data' => $projek,
        ], 201);
    }

    /**
     * Butiran projek.
     */
    public function show(int $id): JsonResponse
    {
        $projek = VideoProject::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'project' => $projek,
                'arahan' => $this->render->arahanRender($projek),
            ],
        ]);
    }

    /**
     * Kemas kini projek.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $projek = VideoProject::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:200'],
            'title' => ['nullable', 'string', 'max:200'],
            'gaya' => ['nullable', 'string', 'max:40'],
            'aspect_ratio' => ['nullable', 'string', 'in:9:16,16:9,1:1,4:5'],
            'frame_rate' => ['nullable', 'integer', 'min:24', 'max:120'],
            'brand_kit_id' => ['nullable', 'integer', 'exists:video_brand_kits,id'],
            'settings' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $projek->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Projek berjaya dikemas kini.',
            'data' => $projek->fresh(),
        ]);
    }

    /**
     * Buang projek.
     */
    public function destroy(int $id): JsonResponse
    {
        VideoProject::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Projek berjaya dibuang.',
        ]);
    }

    /**
     * Jalankan preflight.
     */
    public function preflight(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'skill' => ['required', 'string', 'in:mograph,motion,video-editor'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        return response()->json([
            'success' => true,
            'data' => $this->render->preflight($v->validated()['skill']),
        ]);
    }

    /**
     * Mula render.
     */
    public function render(int $id): JsonResponse
    {
        $projek = VideoProject::findOrFail($id);
        $hasil = $this->render->mula($projek);

        return response()->json([
            'success' => $hasil['success'],
            'message' => $hasil['mesej'],
            'data' => $projek->fresh(),
        ], $hasil['success'] ? 202 : 422);
    }

    /**
     * Kemas kini kemajuan (dipanggil oleh pekerja render).
     */
    public function progress(Request $request, int $id): JsonResponse
    {
        $projek = VideoProject::findOrFail($id);

        $v = Validator::make($request->all(), [
            'status' => ['nullable', 'string', 'in:draft,queued,preflight,rendering,qc,completed,failed,cancelled'],
            'stage' => ['nullable', 'string', 'max:60'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'error_message' => ['nullable', 'string', 'max:2000'],
            'qc_report' => ['nullable', 'array'],
            'duration_seconds' => ['nullable', 'numeric', 'min:0'],
            'output_path' => ['nullable', 'string', 'max:500'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        return response()->json([
            'success' => true,
            'data' => $this->render->kemasKemajuan($projek, $v->validated()),
        ]);
    }

    /**
     * Arahan render untuk sesi agent/Claude.
     */
    public function instructions(int $id): JsonResponse
    {
        $projek = VideoProject::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->render->arahanRender($projek),
        ]);
    }

    /**
     * Hantar video ke MediaEditor untuk sunting lanjutan.
     */
    public function sendToMediaEditor(int $id): JsonResponse
    {
        $projek = VideoProject::findOrFail($id);
        $hasil = $this->render->hantarKeMediaEditor($projek);

        return response()->json([
            'success' => $hasil['success'],
            'message' => $hasil['mesej'],
            'data' => ['media_editor_project_id' => $hasil['project_id']],
        ], $hasil['success'] ? 200 : 422);
    }

    /**
     * Ringkasan penggunaan.
     */
    public function ringkasan(): JsonResponse
    {
        $semua = VideoProject::query();

        return response()->json([
            'success' => true,
            'data' => [
                'jumlah_projek' => (clone $semua)->count(),
                'selesai' => (clone $semua)->where('status', VideoProject::STATUS_COMPLETED)->count(),
                'sedang_proses' => (clone $semua)->whereIn('status', [
                    VideoProject::STATUS_QUEUED,
                    VideoProject::STATUS_PREFLIGHT,
                    VideoProject::STATUS_RENDERING,
                    VideoProject::STATUS_QC,
                ])->count(),
                'gagal' => (clone $semua)->where('status', VideoProject::STATUS_FAILED)->count(),
                'jumlah_minit' => round((float) (clone $semua)->sum('duration_seconds') / 60, 2),
                'mengikut_skill' => (clone $semua)
                    ->selectRaw('skill, COUNT(*) as jumlah')
                    ->groupBy('skill')
                    ->pluck('jumlah', 'skill'),
            ],
        ]);
    }

    /**
     * Senarai brand kit.
     */
    public function brandKits(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => VideoBrandKit::query()->orderByDesc('is_default')->orderBy('name')->get(),
            'meta' => ['font' => VideoBrandKit::senaraiFont()],
        ]);
    }

    /**
     * Cipta brand kit.
     */
    public function storeBrandKit(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:120'],
            'handle' => ['nullable', 'string', 'max:80'],
            'cta' => ['nullable', 'string', 'max:200'],
            'color_primary' => ['nullable', 'string', 'max:9'],
            'color_accent' => ['nullable', 'string', 'max:9'],
            'color_background' => ['nullable', 'string', 'max:9'],
            'font' => ['nullable', 'string', 'max:60'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);

        if (! empty($data['is_default'])) {
            VideoBrandKit::query()->update(['is_default' => false]);
        }

        $kit = VideoBrandKit::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Brand kit berjaya dicipta.',
            'data' => $kit,
        ], 201);
    }

    /**
     * Kemas kini brand kit.
     */
    public function updateBrandKit(Request $request, int $id): JsonResponse
    {
        $kit = VideoBrandKit::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:120'],
            'handle' => ['nullable', 'string', 'max:80'],
            'cta' => ['nullable', 'string', 'max:200'],
            'color_primary' => ['nullable', 'string', 'max:9'],
            'color_accent' => ['nullable', 'string', 'max:9'],
            'color_background' => ['nullable', 'string', 'max:9'],
            'font' => ['nullable', 'string', 'max:60'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        if (! empty($data['is_default'])) {
            VideoBrandKit::query()->where('id', '!=', $kit->id)->update(['is_default' => false]);
        }

        $kit->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Brand kit berjaya dikemas kini.',
            'data' => $kit->fresh(),
        ]);
    }

    /**
     * Buang brand kit.
     */
    public function destroyBrandKit(int $id): JsonResponse
    {
        VideoBrandKit::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand kit berjaya dibuang.',
        ]);
    }

    /**
     * Unduh `aibijan-brand.json`.
     */
    public function brandKitJson(int $id): JsonResponse
    {
        $kit = VideoBrandKit::findOrFail($id);

        return response()->json($kit->keJson())
            ->header('Content-Disposition', 'attachment; filename="aibijan-brand.json"');
    }

    /**
     * @param  \Illuminate\Contracts\Validation\Validator  $v
     */
    protected function ralatValidasi($v): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Input tidak sah.',
            'errors' => $v->errors(),
        ], 422);
    }
}
