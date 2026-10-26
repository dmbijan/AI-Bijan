<?php

namespace Tests\Support;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

/** Pembantu ujian: daftar syarikat, log masuk sebenar (sesi SPA), jemput ahli dengan peranan. */
trait AksesTenant
{
    protected array $pw = [];

    /** Tetapkan konteks tenant untuk ujian yang memanggil servis terus (bukan melalui HTTP). */
    protected function dalamTenant(Tenant $t): void
    {
        app(TenantContext::class)->set($t);
    }

    protected function setUpAkses(): void
    {
        $this->withHeader('Referer', config('app.url'));
    }

    protected function daftar(string $email, string $syarikat): array
    {
        $this->pw[$email] = 'RahsiaKuat123';
        $this->postJson('/api/v1/auth/register', ['name' => 'Pemilik', 'email' => $email, 'password' => 'RahsiaKuat123', 'company' => $syarikat])->assertCreated();

        return [User::where('email', $email)->first(), Tenant::where('name', $syarikat)->first()];
    }

    protected function sebagai(User $u, Tenant $t)
    {
        $this->app->forgetScopedInstances();
        auth()->forgetGuards();
        $this->flushSession();
        $this->postJson('/api/v1/auth/login', ['email' => $u->email, 'password' => $this->pw[$u->email]])->assertOk();
        $this->app->forgetScopedInstances();

        return $this->withHeaders(['X-Tenant' => $t->ulid]);
    }

    protected function jemput(User $owner, Tenant $t, string $email, array $roles): User
    {
        $tok = basename($this->sebagai($owner, $t)->postJson('/api/v1/members/invite', ['email' => $email, 'roles' => $roles])->assertCreated()->json('invite_url'));
        $this->flushSession();
        $this->pw[$email] = 'KataLaluan12345';
        $this->postJson('/api/v1/invitations/accept', ['token' => $tok, 'name' => $email, 'password' => 'KataLaluan12345'])->assertOk();

        return User::where('email', $email)->first();
    }
}
