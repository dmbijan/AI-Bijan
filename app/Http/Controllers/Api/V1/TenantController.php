<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\StoredFile;
use App\Models\TenantModule;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Pentadbiran dalam satu tenant. Semua query melalui skop tenant (BelongsToTenant). */
class TenantController extends Controller
{
    // Konteks & audit diambil semasa digunakan (bukan disuntik melalui constructor): objek pengawal boleh
    // dikongsi antara permintaan, jadi konteks yang disimpan dalam constructor boleh jadi lapuk/tenant salah.
    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    private function pengaudit(): AuditLogger
    {
        return app(AuditLogger::class);
    }

    public function show()
    {
        $t = $this->ctx()->tenant();

        return response()->json([
            'tenant' => ['ulid' => $t->ulid, 'name' => $t->name, 'status' => $t->status, 'timezone' => $t->timezone, 'currency' => $t->currency, 'locale' => $t->locale, 'trial_ends_at' => $t->trial_ends_at],
            'permissions' => $this->ctx()->permissions(),
            'roles' => $this->ctx()->membership()?->roles()->pluck('key') ?? [],
            'modules' => TenantModule::where('enabled', true)->pluck('module'),
            'support_access' => $this->ctx()->isSupportAccess(),
        ]);
    }

    // ---------------- Tetapan
    public function settings()
    {
        return response()->json(Setting::pluck('value', 'key'));
    }

    public function updateSettings(Request $r)
    {
        $d = $r->validate(['settings' => 'required|array|max:50', 'settings.*' => 'nullable', 'name' => 'nullable|string|max:120']);
        DB::transaction(function () use ($d) {
            if (! empty($d['name'])) {
                $t = $this->ctx()->tenant();
                $before = ['name' => $t->name];
                $t->update(['name' => $d['name']]);
                $this->pengaudit()->log('tenant.renamed', $t, $before, ['name' => $d['name']]);
            }
            foreach ($d['settings'] as $k => $v) {
                abort_unless(preg_match('/^[a-z0-9_.]{1,80}$/', $k), 422, "Kunci tetapan tidak sah: $k");
                $s = Setting::firstOrNew(['key' => $k]);
                $before = $s->exists ? ['value' => $s->value] : null;
                $s->value = $v;
                $s->save();
                $this->pengaudit()->log('settings.updated', $s, $before, ['key' => $k, 'value' => $v]);
            }
        });

        return $this->settings();
    }

    // ---------------- Ahli
    public function members()
    {
        return response()->json(TenantUser::with(['user:id,name,email', 'roles:id,key,name'])->orderBy('id')->get()
            ->map(fn ($m) => ['id' => $m->id, 'status' => $m->status, 'name' => $m->user->name, 'email' => $m->user->email, 'roles' => $m->roles->pluck('key'), 'joined_at' => $m->joined_at]));
    }

    /** Jemput ahli: pulangkan pautan jemputan (token dipapar sekali). Penghantaran e-mel ikut tetapan mel tenant. */
    public function invite(Request $r)
    {
        $d = $r->validate(['email' => 'required|email|max:190', 'roles' => 'required|array|min:1', 'roles.*' => 'string']);
        $roles = Role::whereIn('key', $d['roles'])->get();
        abort_if($roles->count() !== count(array_unique($d['roles'])), 422, 'Peranan tidak sah.');
        abort_if($roles->contains('key', 'tenant_owner') && ! $this->ctx()->can('roles.manage'), 403, 'Hanya pemilik boleh melantik pemilik.');
        $token = Str::random(64);
        $m = DB::transaction(function () use ($d, $roles, $token, $r) {
            $u = User::where('email', strtolower($d['email']))->first();
            if (! $u) {
                // Kata laluan rawak yang tidak diketahui sesiapa; pengguna tetapkan sendiri semasa terima jemputan.
                $u = User::create(['email' => strtolower($d['email']), 'name' => Str::before($d['email'], '@'), 'password' => Str::random(48)]);
                $u->forceFill(['must_set_password' => true])->save();
            }
            $m = TenantUser::where('user_id', $u->id)->first();
            abort_if($m && $m->status === 'active', 409, 'Pengguna ini sudah ahli.');
            $m ??= new TenantUser(['user_id' => $u->id]);
            $m->fill(['status' => 'invited', 'invited_by' => $r->user()->id, 'invite_token_hash' => hash('sha256', $token), 'invite_expires_at' => now()->addDays(7)])->save();
            $m->roles()->sync($roles->pluck('id'));
            $this->pengaudit()->log('members.invited', $m, null, ['email' => $u->email, 'roles' => $roles->pluck('key')]);

            return $m;
        });

        return response()->json(['id' => $m->id, 'invite_url' => url('/jemputan/'.$token), 'expires_at' => $m->invite_expires_at], 201);
    }

    public function updateMember(Request $r, int $id)
    {
        $d = $r->validate(['roles' => 'nullable|array|min:1', 'roles.*' => 'string', 'status' => 'nullable|in:active,disabled']);
        $m = TenantUser::with('roles')->findOrFail($id);
        $before = ['roles' => $m->roles->pluck('key'), 'status' => $m->status];
        DB::transaction(function () use ($d, $m, $r) {
            if (isset($d['roles'])) {
                $roles = Role::whereIn('key', $d['roles'])->get();
                abort_if($roles->count() !== count(array_unique($d['roles'])), 422, 'Peranan tidak sah.');
                $jadiPemilik = $roles->contains('key', 'tenant_owner') !== $m->roles->contains('key', 'tenant_owner');
                abort_if($jadiPemilik && ! $this->ctx()->can('roles.manage'), 403, 'Hanya pemilik boleh ubah peranan pemilik.');
                $m->roles()->sync($roles->pluck('id'));
            }
            if (isset($d['status'])) {
                abort_if($m->user_id === $r->user()->id && $d['status'] === 'disabled', 422, 'Tidak boleh nyahaktif diri sendiri.');
                $m->update(['status' => $d['status']]);
            }
            // Mesti kekal sekurang-kurangnya seorang pemilik aktif.
            $pemilik = TenantUser::where('status', 'active')->whereHas('roles', fn ($q) => $q->where('key', 'tenant_owner'))->count();
            abort_if($pemilik < 1, 422, 'Mesti ada sekurang-kurangnya seorang pemilik aktif.');
        });
        $m->refresh()->load('roles');
        $this->pengaudit()->log('members.updated', $m, $before, ['roles' => $m->roles->pluck('key'), 'status' => $m->status]);

        return response()->json(['ok' => true]);
    }

    // ---------------- Peranan
    public function roles()
    {
        return response()->json([
            'roles' => Role::with('permissions:id,key')->orderBy('id')->get()->map(fn ($x) => ['id' => $x->id, 'key' => $x->key, 'name' => $x->name, 'is_system' => $x->is_system, 'permissions' => $x->permissions->pluck('key')]),
            'catalog' => Permission::orderBy('key')->get(['key', 'module', 'description']),
        ]);
    }

    public function saveRole(Request $r, ?int $id = null)
    {
        $d = $r->validate(['name' => 'required|string|max:80', 'key' => 'nullable|string|max:40|regex:/^[a-z0-9_]+$/', 'permissions' => 'array', 'permissions.*' => 'string|exists:permissions,key']);
        $role = $id ? Role::findOrFail($id) : new Role(['key' => $d['key'] ?? Str::slug($d['name'], '_'), 'is_system' => false]);
        abort_if($role->key === 'tenant_owner', 422, 'Peranan pemilik tidak boleh diubah.');
        $before = $role->exists ? ['name' => $role->name, 'permissions' => $role->permissions()->pluck('key')] : null;
        $role->name = $d['name'];
        $role->save();
        $role->permissions()->sync(Permission::whereIn('key', $d['permissions'] ?? [])->pluck('id'));
        $this->pengaudit()->log($id ? 'roles.updated' : 'roles.created', $role, $before, ['name' => $role->name, 'permissions' => $d['permissions'] ?? []]);

        return response()->json(['id' => $role->id], $id ? 200 : 201);
    }

    // ---------------- Audit
    public function audit(Request $r)
    {
        return response()->json(AuditLog::orderByDesc('id')->when($r->query('action'), fn ($q, $a) => $q->where('action', 'like', $a.'%'))
            ->paginate(min((int) $r->query('per_page', 50), 200), ['id', 'user_id', 'action', 'subject_type', 'subject_id', 'before', 'after', 'created_at']));
    }

    // ---------------- Fail (peribadi, muat turun melalui endpoint berizin)
    public function upload(Request $r)
    {
        $c = config('aibijan.upload');
        $r->validate(['file' => 'required|file|max:'.$c['max_kb'].'|mimes:'.implode(',', $c['mimes'])]);
        $f = $r->file('file');
        $t = $this->ctx()->tenant();
        $path = $f->store('tenants/'.$t->ulid.'/'.now()->format('Y/m'), 'local');
        $row = StoredFile::create(['disk' => 'local', 'path' => $path, 'original_name' => Str::limit($f->getClientOriginalName(), 200, ''), 'mime' => $f->getMimeType(),
            'size' => $f->getSize(), 'sha256' => hash_file('sha256', $f->getRealPath()), 'uploaded_by' => $r->user()->id]);
        $this->pengaudit()->log('files.uploaded', $row, null, ['name' => $row->original_name, 'size' => $row->size]);

        return response()->json($row->only(['ulid', 'original_name', 'mime', 'size', 'sha256']), 201);
    }

    public function download(string $ulid)
    {
        $f = StoredFile::where('ulid', $ulid)->firstOrFail();
        $this->pengaudit()->log('files.downloaded', $f);

        return Storage::disk($f->disk)->download($f->path, $f->original_name, ['Content-Type' => $f->mime, 'X-Content-Type-Options' => 'nosniff']);
    }

    // ---------------- Kunci API
    public function apiKeys()
    {
        return response()->json(ApiKey::orderByDesc('id')->get(['id', 'name', 'prefix', 'scopes', 'last_used_at', 'revoked_at', 'created_at']));
    }

    public function createApiKey(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:80', 'scopes' => 'required|array|min:1', 'scopes.*' => 'in:products.read,stock.read,orders.write']);
        $kunci = 'aib_'.Str::random(40);
        $k = ApiKey::create(['name' => $d['name'], 'prefix' => substr($kunci, 0, 10), 'hash' => hash('sha256', $kunci), 'scopes' => $d['scopes'], 'created_by' => $r->user()->id]);
        $this->pengaudit()->log('api_keys.created', $k, null, ['name' => $k->name, 'scopes' => $k->scopes]);

        return response()->json(['id' => $k->id, 'key' => $kunci, 'note' => 'Simpan kunci ini sekarang. Ia tidak akan dipapar lagi.'], 201);
    }

    public function revokeApiKey(int $id)
    {
        $k = ApiKey::findOrFail($id);
        $k->update(['revoked_at' => now()]);
        $this->pengaudit()->log('api_keys.revoked', $k);

        return response()->json(['ok' => true]);
    }

    // ---------------- Kelulusan
    public function approvals(Request $r)
    {
        return response()->json(ApprovalRequest::orderByDesc('id')->when($r->query('status'), fn ($q, $s) => $q->where('status', $s))->limit(200)->get());
    }

    public function decide(Request $r, int $id)
    {
        $d = $r->validate(['decision' => 'required|in:approved,rejected', 'reason' => 'nullable|string|max:255']);
        $a = ApprovalRequest::lockForUpdate()->findOrFail($id);
        abort_unless($a->status === 'pending', 409, 'Permintaan ini sudah diputuskan.');
        abort_if($a->requested_by === $r->user()->id, 403, 'Tidak boleh meluluskan permintaan sendiri.');
        $a->update(['status' => $d['decision'], 'decided_by' => $r->user()->id, 'decided_at' => now(), 'reason' => $d['reason'] ?? null]);
        $this->pengaudit()->log('approvals.'.$d['decision'], $a, ['status' => 'pending'], ['status' => $d['decision']]);

        // Cadangan ejen AI: laksanakan hanya selepas diluluskan; simpan hasil sebenar dalam payload.
        if ($a->type === 'ai.tool' && $d['decision'] === 'approved') {
            $hasil = app(\App\Modules\AIAgents\Tools\AgentToolRunner::class)->laksanaKelulusan($a);
            $a->update(['payload' => $a->payload + ['hasil' => $hasil]]);
        }

        return response()->json($a);
    }
}
