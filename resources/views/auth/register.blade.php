@extends('layouts.auth')

@section('title', 'Daftar Syarikat')

@section('content')
    <h1 style="font-size:var(--text-lg);margin-bottom:4px">Daftar syarikat anda</h1>
    <p class="text-sm text-muted mb-5">Percubaan 14 hari. Tiada kad kredit diperlukan.</p>

    <form id="registerForm" novalidate>
        <div class="field">
            <label class="label" for="name">Nama Anda</label>
            <input class="input" type="text" id="name" required autocomplete="name">
        </div>

        <div class="field">
            <label class="label" for="company">Nama Syarikat</label>
            <input class="input" type="text" id="company" required autocomplete="organization">
        </div>

        <div class="field">
            <label class="label" for="email">E-mel</label>
            <input class="input" type="email" id="email" required autocomplete="email">
        </div>

        <div class="field">
            <label class="label" for="password">Kata Laluan</label>
            <input class="input" type="password" id="password" required autocomplete="new-password" minlength="8">
            <div class="field-hint">Minimum 8 aksara.</div>
        </div>

        <div id="formError" class="alert alert-danger" hidden></div>

        <button class="btn btn-primary btn-block" type="submit" id="submitBtn">Daftar</button>
    </form>

    <p class="text-sm text-muted mt-5" style="text-align:center">
        Sudah ada akaun? <a href="/login">Log masuk</a>
    </p>
@endsection

@push('scripts')
<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn = document.getElementById('submitBtn');
    const errBox = document.getElementById('formError');
    errBox.hidden = true;

    const payload = {
        name: document.getElementById('name').value.trim(),
        company: document.getElementById('company').value.trim(),
        email: document.getElementById('email').value.trim(),
        password: document.getElementById('password').value,
    };

    try {
        await App.withLoading(btn, () => App.register(payload));
        App.toast('Akaun dicipta', 'success', 1500);
        setTimeout(() => location.href = '/app', 400);
    } catch (err) {
        const msg = err.data?.errors
            ? Object.values(err.data.errors).flat()[0]
            : err.message;
        errBox.textContent = msg;
        errBox.hidden = false;
        btn.disabled = false;
    }
});
</script>
@endpush
