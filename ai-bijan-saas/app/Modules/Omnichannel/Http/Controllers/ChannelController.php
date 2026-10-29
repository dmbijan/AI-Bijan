<?php

namespace App\Modules\Omnichannel\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\Omnichannel\Services\ChannelService;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    protected $channelService;

    public function __construct(ChannelService $channelService)
    {
        $this->channelService = $channelService;
    }

    /**
     * List all channels
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'active_only']);
        $channels = $this->channelService->getAll(app(TenantContext::class)->id(), $filters);
        return response()->json($channels);
    }

    /**
     * Get channel by ID
     */
    public function show($id)
    {
        $channel = \App\Modules\Omnichannel\Models\Channel::findOrFail($id);
        
        if ($channel->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($channel);
    }

    /**
     * Create new channel
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:whatsapp,email,facebook_messenger,instagram,telegram,live_chat,sms,twitter_dm',
            'config' => 'nullable|array',
            'is_primary' => 'nullable|boolean',
            'display_name' => 'nullable|string|max:255',
            'avatar_url' => 'nullable|url',
            'phone_number' => 'nullable|string',
            'email_address' => 'nullable|email',
            'social_handle' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'webhook_secret' => 'nullable|string',
            'rate_limit' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ]);

        try {
            $channel = $this->channelService->create([
                'tenant_id' => app(TenantContext::class)->id(),
                'name' => $validated['name'],
                'type' => $validated['type'],
                'config' => $validated['config'] ?? [],
                'is_primary' => $validated['is_primary'] ?? false,
                'display_name' => $validated['display_name'] ?? null,
                'avatar_url' => $validated['avatar_url'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'email_address' => $validated['email_address'] ?? null,
                'social_handle' => $validated['social_handle'] ?? null,
                'webhook_url' => $validated['webhook_url'] ?? null,
                'webhook_secret' => $validated['webhook_secret'] ?? null,
                'rate_limit' => $validated['rate_limit'] ?? null,
                'metadata' => $validated['metadata'] ?? [],
            ]);

            return response()->json($channel, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Create failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update channel
     */
    public function update(Request $request, $id)
    {
        try {
            $channel = $this->channelService->update($id, $request->all());
            return response()->json($channel);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Connect channel
     */
    public function connect($id)
    {
        try {
            $result = $this->channelService->connect($id);
            return response()->json(['status' => 'connected', 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Connect failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Disconnect channel
     */
    public function disconnect($id)
    {
        try {
            $this->channelService->disconnect($id);
            return response()->json(['message' => 'Channel disconnected']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Disconnect failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Test connection
     */
    public function testConnection($id)
    {
        try {
            $channel = \App\Modules\Omnichannel\Models\Channel::findOrFail($id);
            $result = $channel->testConnection();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Test failed: ' . $e->getMessage()], 500);
        }
    }
}
