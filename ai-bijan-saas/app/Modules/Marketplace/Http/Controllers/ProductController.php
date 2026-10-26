<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * List all products with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $products = $this->productService->list(
            $tenantId,
            $request->only(['category', 'vendor_id', 'is_active', 'is_featured', 'search', 'min_price', 'max_price']),
            $request->get('per_page', 20)
        );

        return response()->json($products);
    }

    /**
     * Get a single product by ID.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Record view
        $this->productService->recordView($product);

        return response()->json($product);
    }

    /**
     * Create a new product.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vendor_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'images' => 'nullable|array',
            'specifications' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $product = $this->productService->create(array_merge($request->all(), [
            'tenant_id' => app(TenantContext::class)->id(),
        ]));

        return response()->json($product, 201);
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:100',
            'price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $product = $this->productService->update($product, $request->all());

        return response()->json($product);
    }

    /**
     * Delete a product.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = $this->productService->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->productService->delete($product);

        return response()->json(['message' => 'Product deleted successfully']);
    }

    /**
     * Get featured products.
     */
    public function featured(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $products = $this->productService->getFeatured(
            $tenantId,
            $request->get('limit', 10)
        );

        return response()->json($products);
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|max:255',
        ]);

        $products = $this->productService->search(
            $request->input('q'),
            $request->only(['category', 'min_rating']),
            $request->get('per_page', 20)
        );

        return response()->json($products);
    }

    /**
     * Update stock quantity.
     */
    public function updateStock(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer',
            'operation' => 'required|in:set,add,remove',
        ]);

        $product = $this->productService->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product = $this->productService->updateStock(
            $product,
            $request->input('quantity'),
            $request->input('operation')
        );

        return response()->json($product);
    }

    /**
     * Get low stock products.
     */
    public function lowStock(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $products = $this->productService->getLowStockProducts($tenantId);

        return response()->json($products);
    }

    /**
     * Bulk update product status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array',
            'is_active' => 'required|boolean',
        ]);

        $count = $this->productService->bulkUpdateStatus(
            $request->input('product_ids'),
            $request->input('is_active')
        );

        return response()->json(['message' => "{$count} products updated"]);
    }
}
