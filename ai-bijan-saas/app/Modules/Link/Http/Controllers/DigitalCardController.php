<?php

namespace App\Modules\Link\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Link\Services\DigitalCardService;
use App\Modules\Link\Models\DigitalCard;
use Illuminate\Http\Request;

class DigitalCardController extends Controller
{
    protected $service;

    public function __construct(DigitalCardService $service)
    {
        $this->service = $service;
    }

    /**
     * Display digital cards.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'template', 'search']);
        $cards = $this->service->getCards($request->tenant_id, $filters);

        return response()->json([
            'success' => true,
            'data' => $cards,
        ]);
    }

    /**
     * Store a new digital card.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'holder_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'linkedin' => 'nullable|url',
            'twitter' => 'nullable|url',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'thumbnail' => 'nullable|string',
            'template' => 'nullable|in:business,personal,minimal,creative',
            'custom_css' => 'nullable|string',
            'expiry_date' => 'nullable|date',
        ]);

        $card = $this->service->createCard(array_merge($validated, [
            'tenant_id' => $request->tenant_id,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Digital card created',
            'data' => $card,
        ], 201);
    }

    /**
     * Display a specific digital card.
     */
    public function show(DigitalCard $digitalCard)
    {
        if ($digitalCard->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $digitalCard,
        ]);
    }

    /**
     * Update the digital card.
     */
    public function update(Request $request, DigitalCard $digitalCard)
    {
        if ($digitalCard->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'holder_name' => 'sometimes|string|max:255',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'linkedin' => 'nullable|url',
            'twitter' => 'nullable|url',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'thumbnail' => 'nullable|string',
            'template' => 'sometimes|in:business,personal,minimal,creative',
            'custom_css' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'status' => 'sometimes|in:active,inactive,pending',
        ]);

        $digitalCard->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Digital card updated',
            'data' => $digitalCard,
        ]);
    }

    /**
     * Get vCard content for download.
     */
    public function getVCard(DigitalCard $digitalCard)
    {
        if ($digitalCard->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $vcard = $this->service->generateVCard($digitalCard);

        return response($vcard, 200)
            ->header('Content-Type', 'text/vcard')
            ->header('Content-Disposition', 'attachment; filename="' . $digitalCard->short_code . '.vcf"');
    }

    /**
     * Track card view.
     */
    public function trackView(DigitalCard $digitalCard)
    {
        if ($digitalCard->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $card = $this->service->trackView($digitalCard->id);

        return response()->json([
            'success' => true,
            'data' => $card,
        ]);
    }

    /**
     * Track card share.
     */
    public function trackShare(DigitalCard $digitalCard)
    {
        if ($digitalCard->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $card = $this->service->trackShare($digitalCard->id);

        return response()->json([
            'success' => true,
            'data' => $card,
        ]);
    }

    /**
     * Delete a digital card.
     */
    public function destroy(DigitalCard $digitalCard)
    {
        if ($digitalCard->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $digitalCard->delete();

        return response()->json([
            'success' => true,
            'message' => 'Digital card deleted',
        ]);
    }
}
