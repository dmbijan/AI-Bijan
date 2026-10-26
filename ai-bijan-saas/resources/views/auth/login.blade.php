@extends('layouts.auth')

@section('title', 'Log Masuk')

@section('content')
    <h1 style="font-size:var(--text-lg);margin-bottom:4px">Selamat kembali</h1>
    <p class="text-sm text-muted mb-5">Log masuk ke ruang kerja perniagaan anda.</p>

    <form id="loginForm" novalidate>
        <div class="field" id="emailField">
            <label class="label" for="email">E-mel</label>
            <input class="input" type="email" id="email" name="email" autocomplete="email" required>
            <div class="field-error" hidden></div>
        </div>

        <div class="field" id="passwordField">
            <label class="label" for="password">Kata Laluan</label>
            <input class="input" type="password" id="password" name="password" autocomplete="current-password" required>
            <div class="field-error" hidden></div>
        </div>

        <div id="formError" class="alert alert-danger" hidden></div>

        <button class="btn btn-primary btn-block" type="submit" id="submitBtn">Log Masuk</button>
    </form>

    <p class="text-sm text-muted mt-5" style="text-align:center">
        Belum ada akaun? <a href="/register">Daftar syarikat</a>
    </p>
@endsection

@push('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn = document.getElementById('submitBtn');
    const errBox = document.getElementById('formError');
    errBox.hidden = true;

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    try {
        await App.withLoading(btn, () => App.login(email, password));
        App.toast('Log masuk berjaya', 'success', 1500);
        setTimeout(() => location.href = '/app', 350);
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
