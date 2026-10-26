<?php

namespace App\Modules\Omnichannel\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Omnichannel\Services\InboundWebhookService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected $webhookService;

    public function __construct(InboundWebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle WhatsApp webhook
     */
    public function handleWhatsApp(Request $request)
    {
        return $this->webhookService->handleWhatsApp($request);
    }

    /**
     * Handle Facebook Messenger webhook
     */
    public function handleFacebook(Request $request)
    {
        return $this->webhookService->handleFacebook($request);
    }

    /**
     * Handle Telegram webhook
     */
    public function handleTelegram(Request $request)
    {
        return $this->webhookService->handleTelegram($request);
    }
}
