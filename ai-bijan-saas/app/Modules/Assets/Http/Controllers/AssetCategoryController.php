<?php

namespace App\Modules\Assets\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Assets\Models\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetCategoryController extends Controller
{
    /**
     * List all asset categories for the tenant.
     */
    public function index(Request $request)
    {
        $query = AssetCategory::forTenant($request->tenant_id);

        if ($request->boolean('active_only')) {
            $query->active();
        }

        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Show a single category.
     */
    public function show(Request $request, int $id)
    {
        $category = AssetCategory::forTenant($request->tenant_id)->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        return response()->json($category);
    }

    /**
     * Create a new category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:asset_categories,name,tenant_id,' . $request->tenant_id,
            'description' => 'nullable|string',
            'default_purchase_price' => 'nullable|numeric|min:0',
            'default_useful_life_years' => 'nullable|integer|min:1|max:50',
            'depreciation_method' => 'nullable|in:straight_line,declining_balance,units_of_production,sum_years_digits',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $request->tenant_id;
        $validated['code'] = AssetCategory::generateCode($validated['name']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $category = AssetCategory::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, int $id)
    {
        $category = AssetCategory::forTenant($request->tenant_id)->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:asset_categories,name,' . $id . ',id,tenant_id,' . $request->tenant_id,
            'description' => 'nullable|string',
            'default_purchase_price' => 'nullable|numeric|min:0',
            'default_useful_life_years' => 'nullable|integer|min:1|max:50',
            'depreciation_method' => 'nullable|in:straight_line,declining_balance,units_of_production,sum_years_digits',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Delete a category (only if no assets are using it).
     */
    public function destroy(Request $request, int $id)
    {
        $category = AssetCategory::forTenant($request->tenant_id)->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        // Check if any assets are using this category
        $assetCount = \App\Modules\Assets\Models\Asset::where('category_id', $id)->count();
        if ($assetCount > 0) {
            return response()->json([
                'message' => "Cannot delete category with {$assetCount} associated assets."
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
