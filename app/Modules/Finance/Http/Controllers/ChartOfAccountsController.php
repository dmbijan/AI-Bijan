<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsController extends Controller
{
    /**
     * ID tenant semasa — MESTI dari konteks, BUKAN header klien.
     * ⚠️ 26 Okt 2026: kod lama guna `$request->header('X-Tenant-ID')` yang
     * membolehkan IDOR — pengguna boleh baca COA tenant lain dengan menukar
     * header. Sentiasa guna TenantContext.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    /**
     * List chart of accounts.
     * GET /api/finance/chart-of-accounts
     */
    public function index(Request $request)
    {
        $query = DB::table('chart_of_accounts')
            ->where('tenant_id', $this->tenantId())
            ->orderBy('sort_order')
            ->orderBy('code');
        
        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('subtype')) {
            $query->where('subtype', $request->subtype);
        }
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
            });
        }
        
        return response()->json($query->paginate(50));
    }

    /**
     * Get single account.
     * GET /api/finance/chart-of-accounts/{id}
     */
    public function show($id, Request $request)
    {
        $account = DB::table('chart_of_accounts')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->first();
        
        if (! $account) {
            return response()->json(['message' => 'Account not found'], 404);
        }
        
        return response()->json($account);
    }

    /**
     * Create new account.
     * POST /api/finance/chart-of-accounts
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:chart_of_accounts,code,NULL,tenant_id,tenant_id,' . $this->tenantId(),
            'name' => 'required|string|max:255',
            'name_bm' => 'nullable|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense,cost_of_goods',
            'subtype' => 'nullable|in:current_asset,non_current_asset,cash,bank,accounts_receivable,inventory,current_liability,non_current_liability,accounts_payable,operating_revenue,other_income,operating_expense,depreciation,tax_expense',
            'parent_id' => 'nullable|integer|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'allow_posting' => 'boolean',
        ]);
        
        $validated['tenant_id'] = $this->tenantId();
        $validated['is_active'] = true;
        $validated['is_system'] = false;
        $validated['normal_balance'] = in_array($validated['type'], ['asset', 'expense', 'cost_of_goods']) ? 'debit' : 'credit';
        
        $account = DB::table('chart_of_accounts')->insertGetId($validated);
        
        return response()->json(['id' => $account, 'message' => 'Account created'], 201);
    }

    /**
     * Update account.
     * PUT /api/finance/chart-of-accounts/{id}
     */
    public function update(Request $request, $id)
    {
        $account = DB::table('chart_of_accounts')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->first();
        
        if (! $account) {
            return response()->json(['message' => 'Account not found'], 404);
        }
        
        // System accounts cannot be modified
        if ($account->is_system) {
            return response()->json(['message' => 'System accounts cannot be modified'], 403);
        }
        
        $validated = $request->validate([
            'code' => 'sometimes|string|max:20|unique:chart_of_accounts,code,' . $id . ',id,tenant_id,' . $this->tenantId(),
            'name' => 'sometimes|string|max:255',
            'name_bm' => 'nullable|string|max:255',
            'type' => 'sometimes|in:asset,liability,equity,revenue,expense,cost_of_goods',
            'subtype' => 'nullable|in:current_asset,non_current_asset,cash,bank,accounts_receivable,inventory,current_liability,non_current_liability,accounts_payable,operating_revenue,other_income,operating_expense,depreciation,tax_expense',
            'parent_id' => 'nullable|integer|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'allow_posting' => 'boolean',
            'is_active' => 'boolean',
        ]);
        
        DB::table('chart_of_accounts')
            ->where('id', $id)
            ->update($validated);
        
        return response()->json(['message' => 'Account updated']);
    }
}
