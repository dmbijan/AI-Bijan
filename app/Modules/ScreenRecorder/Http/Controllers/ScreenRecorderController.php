<?php

namespace App\Modules\ScreenRecorder\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ScreenRecorder\Models\ScreenRecording;
use App\Modules\ScreenRecorder\Services\ScreenRecorderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ScreenRecorderController extends Controller
{
    public function __construct(private ScreenRecorderService $recorder) {}

    /**
     * Senarai rakaman.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ScreenRecording::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('privacy')) {
            $query->where('privacy', $request->input('privacy'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 50))->get(),
            'meta' => ['privasi' => ScreenRecording::privasi()],
        ]);
    }

    /**
     * Muat naik rakaman.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:'.((int) config('screen_recorder.limits.max_file_mb', 500) * 1024)],
            'title' => ['nullable', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_seconds' => ['nullable', 'numeric', 'min:0'],
            'resolution' => ['nullable', 'string', 'max:20'],
            'frame_rate' => ['nullable', 'integer', 'min:1', 'max:120'],
            'has_audio' => ['nullable', 'boolean'],
            'has_mouse_cursor' => ['nullable', 'boolean'],
            'privacy' => ['nullable', 'string', 'in:public,unlisted,private'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $file = $request->file('video');

        $recording = $this->recorder->registerRecording([
            'title' => $data['title'] ?? 'Rakaman Skrin',
            'description' => $data['description'] ?? null,
            'duration_seconds' => (float) ($data['duration_seconds'] ?? 0),
            'resolution' => $data['resolution'] ?? config('screen_recorder.recording.default_resolution'),
            'frame_rate' => (int) ($data['frame_rate'] ?? config('screen_recorder.recording.frame_rate', 30)),
            'has_audio' => (bool) ($data['has_audio'] ?? config('screen_recorder.recording.include_audio', true)),
            'has_mouse_cursor' => (bool) ($data['has_mouse_cursor'] ?? config('screen_recorder.recording.include_cursor', true)),
            'privacy' => $data['privacy'] ?? config('screen_recorder.sharing.default_privacy', 'private'),
            'file_size_bytes' => $file->getSize(),
        ]);

        $path = $this->recorder->storeFile($recording, $file->getRealPath(), $file->getClientOriginalExtension() ?: 'mp4');

        $recording = $this->recorder->completeRecording($recording, [
            'recording_path' => $path,
            'recording_url' => $path ? Storage::disk('public')->url($path) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rakaman berjaya dimuat naik.',
            'data' => [
                'id' => $recording->id,
                'uuid' => $recording->uuid,
                'status' => $recording->status,
                'recording_url' => $recording->recording_url,
                'share' => $this->recorder->shareLink($recording),
            ],
        ], 201);
    }

    /**
     * Butiran rakaman.
     */
    public function show(int $id): JsonResponse
    {
        $recording = ScreenRecording::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'recording' => $recording,
                'share' => $this->recorder->shareLink($recording),
            ],
        ]);
    }

    /**
     * Kemas kini maklumat rakaman.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $recording = ScreenRecording::findOrFail($id);

        $v = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:2000'],
            'privacy' => ['nullable', 'string', 'in:public,unlisted,private'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        if (isset($data['privacy'])) {
            $data['privacy'] = $this->recorder->setPrivacy($recording, $data['privacy'])->privacy;
        }

        $recording->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Rakaman berjaya dikemas kini.',
            'data' => $recording->fresh(),
        ]);
    }

    /**
     * Buang rakaman.
     */
    public function destroy(int $id): JsonResponse
    {
        $recording = ScreenRecording::findOrFail($id);
        $this->recorder->deleteRecording($recording);

        return response()->json([
            'success' => true,
            'message' => 'Rakaman berjaya dibuang.',
        ]);
    }

    /**
     * Pautan perkongsian.
     */
    public function share(int $id): JsonResponse
    {
        $recording = ScreenRecording::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->recorder->shareLink($recording),
        ]);
    }

    /**
     * Jana semula token perkongsian.
     */
    public function regenerateToken(int $id): JsonResponse
    {
        $recording = ScreenRecording::findOrFail($id);
        $recording = $this->recorder->regenerateToken($recording);

        return response()->json([
            'success' => true,
            'message' => 'Token perkongsian berjaya dijana semula.',
            'data' => $this->recorder->shareLink($recording),
        ]);
    }

    /**
     * Komen pada rakaman.
     */
    public function comments(int $id): JsonResponse
    {
        $recording = ScreenRecording::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->recorder->comments($recording),
        ]);
    }

    /**
     * Tambah komen.
     */
    public function storeComment(Request $request, int $id): JsonResponse
    {
        $recording = ScreenRecording::findOrFail($id);

        $v = Validator::make($request->all(), [
            'body' => ['required', 'string', 'max:2000'],
            'timestamp_seconds' => ['nullable', 'numeric', 'min:0'],
            'x_position' => ['nullable', 'numeric'],
            'y_position' => ['nullable', 'numeric'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $komen = $this->recorder->addComment($recording, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Komen berjaya ditambah.',
            'data' => $komen,
        ], 201);
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
