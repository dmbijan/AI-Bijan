<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * List reviews for a product.
     */
    public function index(Request $request, int $productId): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $reviews = $this->reviewService->list(
            $tenantId,
            $productId,
            $request->only(['rating', 'verified_only']),
            $request->get('per_page', 20)
        );

        return response()->json($reviews);
    }

    /**
     * Get review statistics for a product.
     */
    public function statistics(Request $request, int $productId): JsonResponse
    {
        $stats = $this->reviewService->getStatistics($productId);

        return response()->json($stats);
    }

    /**
     * Create a new review.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
            'order_id' => 'nullable|integer',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'images' => 'nullable|array',
        ]);

        // Check if user already reviewed this product
        if ($this->reviewService->hasReviewed(
            app(TenantContext::class)->id(),
            $request->user()->id,
            $request->input('product_id')
        )) {
            return response()->json(['message' => 'You have already reviewed this product'], 400);
        }

        try {
            $review = $this->reviewService->create(array_merge($request->all(), [
                'tenant_id' => app(TenantContext::class)->id(),
                'user_id' => $request->user()->id,
            ]));

            return response()->json($review, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create review: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a review.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $review = $this->reviewService->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        // Only allow users to update their own reviews
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'images' => 'nullable|array',
        ]);

        $review = $this->reviewService->update($review, $request->all());

        return response()->json($review);
    }

    /**
     * Delete a review.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $review = $this->reviewService->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        // Only allow users to delete their own reviews
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->reviewService->delete($review);

        return response()->json(['message' => 'Review deleted']);
    }

    /**
     * Approve a review (admin only).
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $review = $this->reviewService->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review = $this->reviewService->approve($review);

        return response()->json(['message' => 'Review approved', 'review' => $review]);
    }

    /**
     * Reject a review (admin only).
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $review = $this->reviewService->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review = $this->reviewService->reject($review);

        return response()->json(['message' => 'Review rejected', 'review' => $review]);
    }

    /**
     * Mark a review as helpful.
     */
    public function markHelpful(Request $request, int $id): JsonResponse
    {
        $review = $this->reviewService->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review = $this->reviewService->markHelpful($review);

        return response()->json(['message' => 'Review marked as helpful', 'review' => $review]);
    }

    /**
     * Get pending reviews awaiting moderation (admin only).
     */
    public function pending(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $reviews = $this->reviewService->getPendingReviews(
            $tenantId,
            $request->get('per_page', 20)
        );

        return response()->json($reviews);
    }

    /**
     * Bulk approve reviews (admin only).
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $request->validate([
            'review_ids' => 'required|array',
        ]);

        $count = $this->reviewService->bulkApprove($request->input('review_ids'));

        return response()->json(['message' => "{$count} reviews approved"]);
    }

    /**
     * Bulk reject reviews (admin only).
     */
    public function bulkReject(Request $request): JsonResponse
    {
        $request->validate([
            'review_ids' => 'required|array',
        ]);

        $count = $this->reviewService->bulkReject($request->input('review_ids'));

        return response()->json(['message' => "{$count} reviews rejected"]);
    }

    /**
     * Get most reviewed products.
     */
    public function mostReviewed(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $products = $this->reviewService->getMostReviewedProducts(
            $tenantId,
            $request->get('limit', 10)
        );

        return response()->json($products);
    }
}
