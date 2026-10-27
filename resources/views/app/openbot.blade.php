@extends('layouts.app')

@section('title', 'OpenBot & Chat')

@section('content')
<style>
    .openbot-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(320px, .85fr); gap: 16px; align-items: start; }
    .openbot-frame { width: 100%; min-height: 620px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--surface-1); }
    .chat-messages { display: flex; flex-direction: column; gap: 12px; min-height: 280px; max-height: 460px; overflow-y: auto; padding: 14px; }
    .chat-message { max-width: 90%; padding: 10px 12px; border: 1px solid var(--border); border-radius: var(--radius-md); white-space: pre-wrap; overflow-wrap: anywhere; font-size: 13px; }
    .chat-message.user { align-self: flex-end; background: var(--surface-2); }
    .chat-message.assistant { align-self: flex-start; }
    .chat-compose { display: flex; gap: 10px; align-items: end; }
    .chat-compose .textarea { flex: 1; }
    @media (max-width: 1024px) { .openbot-grid { grid-template-columns: 1fr; } .openbot-frame { min-height: 520px; } }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">OpenBot &amp; Chat AI Bijan</h1>
        <p class="page-desc">Akses OpenBot dan gunakan chat AI Bijan melalui AI Gateway dengan kawalan tenant sedia ada.</p>
    </div>
</div>

<div class="openbot-grid">
    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">OpenBot</h2>
                <p class="card-subtitle">Panel ini memerlukan URL servis OpenBot dan konfigurasi embedding/SSO pada deployment.</p>
            </div>
            @if (config('openbot.url'))
                <a class="btn btn-sm" href="{{ config('openbot.url') }}" target="_blank" rel="noopener noreferrer">Buka OpenBot</a>
            @endif
        </div>
        @if (config('openbot.url'))
            <iframe class="openbot-frame" src="{{ config('openbot.url') }}" title="OpenBot" loading="lazy" referrerpolicy="strict-origin"></iframe>
        @else
            <div class="empty">
                <div class="empty-title">OpenBot belum dikonfigurasi</div>
                <div class="empty-text">Tetapkan OPENBOT_URL pada persekitaran pelayan dan pastikan servis OpenBot membenarkan embedding dari origin AI Bijan.</div>
            </div>
        @endif
    </section>

    <section class="card" aria-labelledby="native-chat-title">
        <div class="card-header">
            <div>
                <h2 class="card-title" id="native-chat-title">Chat native AI Bijan</h2>
                <p class="card-subtitle">Mesej diproses oleh AIGateway; kunci provider tidak dihantar ke pelayar.</p>
            </div>
            <button type="button" class="btn btn-sm" id="newChatBtn">Chat baharu</button>
        </div>

        <div class="field">
            <label class="label" for="openbotTaskType">Jenis tugasan</label>
            <select class="select" id="openbotTaskType">
                <option value="quick">Pantas</option>
                <option value="reasoning">Penaakulan</option>
                <option value="coding">Coding</option>
                <option value="creative">Kreatif</option>
                <option value="multilingual">Multibahasa</option>
            </select>
        </div>

        <div id="openbotMessages" class="chat-messages" role="log" aria-live="polite" aria-label="Sejarah chat">
            <p class="text-sm text-muted" id="chatEmpty">Mulakan perbualan. Sejarah ini hanya disimpan sepanjang halaman dibuka.</p>
        </div>

        <form id="openbotChatForm" class="chat-compose">
            <textarea class="textarea" id="openbotPrompt" rows="2" maxlength="12000" placeholder="Tulis mesej..." required></textarea>
            <button class="btn btn-primary" id="openbotSendBtn" type="submit">Hantar</button>
        </form>
        <p class="field-hint mt-3">Respons AI bergantung pada konfigurasi model dan kredit tenant.</p>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.querySelector('#openbotChatForm');
    const input = document.querySelector('#openbotPrompt');
    const messagesEl = document.querySelector('#openbotMessages');
    const sendButton = document.querySelector('#openbotSendBtn');
    const chat = [];

    function renderMessage(role, content) {
        document.querySelector('#chatEmpty')?.remove();
        const message = document.createElement('div');
        message.className = `chat-message ${role}`;
        message.textContent = content;
        messagesEl.append(message);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        return message;
    }

    document.querySelector('#newChatBtn').addEventListener('click', () => {
        chat.length = 0;
        messagesEl.replaceChildren();
        const empty = document.createElement('p');
        empty.id = 'chatEmpty';
        empty.className = 'text-sm text-muted';
        empty.textContent = 'Mulakan perbualan. Sejarah ini hanya disimpan sepanjang halaman dibuka.';
        messagesEl.append(empty);
        input.focus();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const content = input.value.trim();
        if (!content || sendButton.disabled) return;

        chat.push({ role: 'user', content });
        renderMessage('user', content);
        input.value = '';
        sendButton.disabled = true;
        const pending = renderMessage('assistant', 'Sedang menjana respons…');

        try {
            const response = await App.api.post('/api/v1/ai-gateway/chat', {
                messages: chat.slice(-12),
                task_type: document.querySelector('#openbotTaskType').value,
                source_module: 'openbot_native_chat',
                max_output_tokens: 800,
            });
            const answer = response.content || 'AI tidak memulangkan kandungan respons.';
            pending.textContent = answer;
            chat.push({ role: 'assistant', content: answer });
        } catch (error) {
            pending.textContent = error.message || 'Chat gagal. Sila cuba lagi.';
            App.toast(pending.textContent, 'error');
        } finally {
            sendButton.disabled = false;
            input.focus();
        }
    });

    App.loadSession().then((ok) => {
        if (!ok) window.location.href = '/login';
    });
})();
</script>
@endpush
