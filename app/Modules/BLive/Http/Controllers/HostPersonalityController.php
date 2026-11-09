<?php

namespace App\Modules\BLive\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BLive\Models\HostPersonality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HostPersonalityController extends Controller
{
    /**
     * Senarai personaliti hos.
     */
    public function index(Request $request): JsonResponse
    {
        $query = HostPersonality::query()->orderBy('name');

        if ($request->filled('archetype')) {
            $query->where('archetype', $request->input('archetype'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 100))->get(),
            'meta' => ['archetypes' => HostPersonality::archetypes()],
        ]);
    }

    /**
     * Butiran personaliti.
     */
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => HostPersonality::findOrFail($id),
        ]);
    }

    /**
     * Cipta personaliti.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'archetype' => ['nullable', 'string', 'max:60'],
            'primary_language' => ['nullable', 'string', 'max:5'],
            'locale' => ['nullable', 'string', 'max:10'],
            'supported_languages' => ['nullable', 'array'],
            'system_instructions' => ['nullable', 'string', 'max:4000'],
            'tone_profile' => ['nullable', 'array'],
            'speaking_rate' => ['nullable', 'numeric', 'min:0.25', 'max:2.0'],
            'allow_code_switching' => ['nullable', 'boolean'],
            'max_speech_characters' => ['nullable', 'integer', 'min:100', 'max:5000'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'safety_policies' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(6);
        $data['created_by'] = auth()->id();

        if (! empty($data['is_default'])) {
            HostPersonality::query()->update(['is_default' => false]);
        }

        $personaliti = HostPersonality::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Personaliti hos berjaya dicipta.',
            'data' => $personaliti,
        ], 201);
    }

    /**
     * Kemas kini personaliti.
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $personaliti = HostPersonality::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'archetype' => ['nullable', 'string', 'max:60'],
            'primary_language' => ['nullable', 'string', 'max:5'],
            'locale' => ['nullable', 'string', 'max:10'],
            'supported_languages' => ['nullable', 'array'],
            'system_instructions' => ['nullable', 'string', 'max:4000'],
            'tone_profile' => ['nullable', 'array'],
            'speaking_rate' => ['nullable', 'numeric', 'min:0.25', 'max:2.0'],
            'allow_code_switching' => ['nullable', 'boolean'],
            'max_speech_characters' => ['nullable', 'integer', 'min:100', 'max:5000'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'safety_policies' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        if (! empty($data['is_default'])) {
            HostPersonality::query()->where('id', '!=', $personaliti->id)->update(['is_default' => false]);
        }

        $personaliti->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Personaliti hos berjaya dikemas kini.',
            'data' => $personaliti->fresh(),
        ]);
    }

    /**
     * Buang personaliti.
     */
    public function destroy(int $id): JsonResponse
    {
        $personaliti = HostPersonality::findOrFail($id);
        $personaliti->delete();

        return response()->json([
            'success' => true,
            'message' => 'Personaliti hos berjaya dibuang.',
        ]);
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
