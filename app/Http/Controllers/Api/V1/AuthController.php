<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TenantProvisioner;
use App\Services\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Daftar syarikat baru: pengguna + tenant + peranan pemilik. */
    public function register(Request $r, TenantProvisioner $prov)
    {
        abort_unless($r->hasSession(), 400, 'Log masuk SPA perlukan sesi dari asal aplikasi.');
        $d = $r->validate([
            'name' => 'required|string|max:120', 'email' => 'required|email:rfc|max:190|unique:users,email',
            'password' => ['required', Password::min(10)->letters()->numbers()], 'company' => 'required|string|max:120',
        ]);
        $user = DB::transaction(function () use ($d, $prov) {
            $u = User::create(['name' => $d['name'], 'email' => strtolower($d['email']), 'password' => $d['password']]);
            $prov->create($d['company'], $u);

            return $u;
        });
        $r->session()->invalidate(); // buang data sesi pengguna sebelum ini (cth. hash kata laluan lama)
        Auth::guard('web')->login($user);
        $r->session()->regenerate();

        return response()->json($this->profil($user), 201);
    }

    public function login(Request $r, AuditLogger $audit)
    {
        abort_unless($r->hasSession(), 400, 'Log masuk SPA perlukan sesi dari asal aplikasi.');
        $d = $r->validate(['email' => 'required|email', 'password' => 'required|string', 'code' => 'nullable|string|max:10']);
        $kunci = 'login:'.strtolower($d['email']).'|'.$r->ip();
        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages(['email' => 'Terlalu banyak cubaan. Cuba lagi dalam '.RateLimiter::availableIn($kunci).' saat.'])->status(429);
        }
        $u = User::where('email', strtolower($d['email']))->first();
        if (! $u || ! $u->is_active || $u->must_set_password || ! Hash::check($d['password'], $u->password)) {
            RateLimiter::hit($kunci, 300);
            throw ValidationException::withMessages(['email' => 'E-mel atau kata laluan salah.']);
        }
        if ($u->two_factor_confirmed_at) {
            if (empty($d['code'])) {
                return response()->json(['two_factor' => true], 202);
            }
            if (! Totp::verify($u->two_factor_secret, $d['code'])) {
                RateLimiter::hit($kunci, 300);
                throw ValidationException::withMessages(['code' => 'Kod 2FA salah.']);
            }
        }
        RateLimiter::clear($kunci);
        $r->session()->invalidate(); // buang data sesi pengguna sebelum ini (cth. hash kata laluan lama)
        Auth::guard('web')->login($u);
        $r->session()->regenerate();
        if ($u->two_factor_confirmed_at) {
            $r->session()->put('2fa_ok', $u->id);
        }
        $u->forceFill(['last_login_at' => now()])->save();
        $audit->log('auth.login', $u, null, null, null);

        return response()->json($this->profil($u));
    }

    public function logout(Request $r)
    {
        Auth::guard('web')->logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $r)
    {
        return response()->json($this->profil($r->user()));
    }

    /** Pilih tenant aktif (mesti ahli aktif). */
    public function pilihTenant(Request $r)
    {
        $d = $r->validate(['tenant' => 'required|string|size:26']);
        $ada = TenantUser::withoutGlobalScope('tenant')->where('user_id', $r->user()->id)->where('status', 'active')
            ->whereHas('tenant', fn ($t) => $t->where('ulid', $d['tenant']))->exists();
        abort_unless($ada, 403, 'Anda bukan ahli syarikat ini.');
        $r->session()->put('tenant_ulid', $d['tenant']);

        return response()->json(['tenant' => $d['tenant']]);
    }

    public function twoFactorSetup(Request $r)
    {
        $u = $r->user();
        abort_if($u->two_factor_confirmed_at, 409, '2FA sudah aktif.');
        $secret = Totp::secret();
        $u->forceFill(['two_factor_secret' => $secret])->save();

        return response()->json(['secret' => $secret, 'uri' => Totp::uri($secret, $u->email)]);
    }

    public function twoFactorConfirm(Request $r, AuditLogger $audit)
    {
        $d = $r->validate(['code' => 'required|string|max:10']);
        $u = $r->user();
        abort_unless($u->two_factor_secret && Totp::verify($u->two_factor_secret, $d['code']), 422, 'Kod salah.');
        $u->forceFill(['two_factor_confirmed_at' => now()])->save();
        $r->session()->put('2fa_ok', $u->id);
        $audit->log('auth.2fa_enabled', $u);

        return response()->json(['ok' => true]);
    }

    /** Terima jemputan (awam, dengan token sekali guna). */
    public function terimaJemputan(Request $r, AuditLogger $audit)
    {
        abort_unless($r->hasSession(), 400, 'Perlukan sesi dari asal aplikasi.');
        $d = $r->validate(['token' => 'required|string|size:64', 'name' => 'nullable|string|max:120', 'password' => ['nullable', Password::min(10)->letters()->numbers()]]);
        $m = TenantUser::withoutGlobalScope('tenant')->where('invite_token_hash', hash('sha256', $d['token']))->where('status', 'invited')->first();
        abort_unless($m && $m->invite_expires_at?->isFuture(), 404, 'Jemputan tidak sah atau tamat tempoh.');
        $u = $m->user;
        if ($u->must_set_password) {
            abort_unless(! empty($d['password']) && ! empty($d['name']), 422, 'Isi nama dan kata laluan.');
            $u->forceFill(['name' => $d['name'], 'password' => $d['password'], 'must_set_password' => false, 'email_verified_at' => now()])->save();
        }
        $m->forceFill(['status' => 'active', 'joined_at' => now(), 'invite_token_hash' => null, 'invite_expires_at' => null])->save();
        $audit->log('members.joined', $m, null, ['user' => $u->email], $m->tenant_id);
        $r->session()->invalidate(); // buang data sesi pengguna sebelum ini (cth. hash kata laluan lama)
        Auth::guard('web')->login($u);
        $r->session()->regenerate();

        return response()->json($this->profil($u));
    }

    private function profil(User $u): array
    {
        return [
            'user' => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'locale' => $u->locale, 'platform_admin' => $u->is_platform_admin, 'two_factor' => (bool) $u->two_factor_confirmed_at],
            'tenants' => $u->memberships()->where('status', 'active')->with(['tenant:id,ulid,name,status'])->get()
                ->map(fn ($m) => ['ulid' => $m->tenant->ulid, 'name' => $m->tenant->name, 'status' => $m->tenant->status])->values(),
        ];
    }
}
