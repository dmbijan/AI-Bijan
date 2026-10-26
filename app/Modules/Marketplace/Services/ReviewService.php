<?php

namespace App\Modules\Marketplace\Services;

use App\Modules\Marketplace\Models\Review;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Get all reviews for a product with pagination.
     */
    public function list(int $tenantId, int $productId, array $filters = [], int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Review::where('tenant_id', $tenantId)
            ->where('product_id', $productId);

        // Only show approved reviews by default
        if (!isset($filters['include_unapproved']) || !$filters['include_unapproved']) {
            $query->approved();
        }

        if (!empty($filters['rating'])) {
            $query->byRating($filters['rating']);
        }

        if (isset($filters['verified_only']) && $filters['verified_only']) {
            $query->where('is_verified_purchase', true);
        }

        return $query->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all reviews by a user.
     */
    public function listByUser(int $tenantId, int $userId, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Review::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->with(['product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get a single review by ID.
     */
    public function find(int $reviewId): ?Review
    {
        return Review::with(['user', 'product'])->find($reviewId);
    }

    /**
     * Create a new review.
     */
    public function create(array $data): Review
    {
        return DB::transaction(function () use ($data) {
            $review = Review::create([
                'tenant_id' => $data['tenant_id'],
                'product_id' => $data['product_id'],
                'user_id' => $data['user_id'],
                'order_id' => $data['order_id'] ?? null,
                'rating' => $data['rating'],
                'title' => $data['title'] ?? '',
                'comment' => $data['comment'] ?? '',
                'images' => $data['images'] ?? [],
                'is_verified_purchase' => isset($data['order_id']),
                'is_approved' => config('marketplace.reviews.moderation_required', true),
            ]);

            // Update product rating and review count
            $this->updateProductRating($data['product_id']);

            return $review->fresh(['user', 'product']);
        });
    }

    /**
     * Update an existing review.
     */
    public function update(Review $review, array $data): Review
    {
        $review->update($data);
        
        // Update product rating if rating or images changed
        if (isset($data['rating']) || isset($data['images'])) {
            $this->updateProductRating($review->product_id);
        }

        return $review->fresh(['user', 'product']);
    }

    /**
     * Delete a review.
     */
    public function delete(Review $review): bool
    {
        $productId = $review->product_id;
        $result = $review->delete();
        
        // Update product rating after deletion
        $this->updateProductRating($productId);

        return $result;
    }

    /**
     * Approve a review.
     */
    public function approve(Review $review): Review
    {
        $review->update(['is_approved' => true]);
        return $review->fresh();
    }

    /**
     * Reject a review.
     */
    public function reject(Review $review): Review
    {
        $review->update(['is_approved' => false]);
        return $review->fresh();
    }

    /**
     * Mark a review as helpful.
     */
    public function markHelpful(Review $review): Review
    {
        $review->markHelpful();
        return $review->fresh();
    }

    /**
     * Update product rating based on all approved reviews.
     */
    public function updateProductRating(int $productId): void
    {
        $product = \App\Modules\Marketplace\Models\Product::find($productId);

        if (!$product) {
            return;
        }

        $reviews = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->get();

        $reviewCount = $reviews->count();
        $avgRating = $reviewCount > 0 ? round($reviews->avg('rating'), 2) : 0;

        $product->update([
            'rating' => $avgRating,
            'review_count' => $reviewCount,
        ]);
    }

    /**
     * Get review statistics for a product.
     */
    public function getStatistics(int $productId): array
    {
        $reviews = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->get();

        $totalReviews = $reviews->count();
        
        // Rating distribution
        $distribution = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        $avgRating = $totalReviews > 0 ? round($reviews->avg('rating'), 2) : 0;
        $verifiedCount = $reviews->where('is_verified_purchase', true)->count();

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => $avgRating,
            'rating_distribution' => $distribution,
            'verified_purchases' => $verifiedCount,
            'recommendation_percentage' => $totalReviews > 0 
                ? round(($reviews->where('rating', '>=', 4)->count() / $totalReviews) * 100, 2) 
                : 0,
        ];
    }

    /**
     * Get pending reviews awaiting moderation.
     */
    public function getPendingReviews(int $tenantId, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Review::where('tenant_id', $tenantId)
            ->where('is_approved', false)
            ->with(['user', 'product'])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Bulk approve reviews.
     */
    public function bulkApprove(array $reviewIds): int
    {
        return Review::whereIn('id', $reviewIds)
            ->where('is_approved', false)
            ->update(['is_approved' => true]);
    }

    /**
     * Bulk reject reviews.
     */
    public function bulkReject(array $reviewIds): int
    {
        return Review::whereIn('id', $reviewIds)
            ->where('is_approved', false)
            ->update(['is_approved' => false]);
    }

    /**
     * Check if user has already reviewed a product.
     */
    public function hasReviewed(int $tenantId, int $userId, int $productId): bool
    {
        return Review::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Get most reviewed products.
     */
    public function getMostReviewedProducts(int $tenantId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Modules\Marketplace\Models\Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->withCount(['reviews as approved_review_count' => function ($query) {
                $query->where('is_approved', true);
            }])
            ->orderByDesc('approved_review_count')
            ->limit($limit)
            ->get();
    }
}
