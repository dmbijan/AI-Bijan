<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Marketplace Vendors table
        Schema::create('marketplace_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->decimal('commission_rate', 5, 4)->default(0.05);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_sales')->default(0);
            $table->integer('total_products')->default(0);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('slug');
            $table->index(['is_active', 'is_verified']);
        });

        // Marketplace Products table
        Schema::create('marketplace_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('marketplace_vendors')->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('category')->index();
            $table->string('subcategory')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->json('images')->nullable();
            $table->json('specifications')->nullable();
            $table->json('tags')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('sales_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('slug');
            $table->index(['is_active', 'is_featured']);
            $table->index('vendor_id');
        });

        // Marketplace Orders table
        Schema::create('marketplace_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('order_no')->unique();
            $table->foreignId('vendor_id')->nullable()->constrained('marketplace_vendors')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('currency')->default('MYR');
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('shipping_method')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('order_no');
            $table->index('status');
            $table->index('payment_status');
            $table->index('vendor_id');
            $table->index('customer_id');
        });

        // Marketplace Order Items table
        Schema::create('marketplace_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('marketplace_products')->onDelete('set null');
            $table->foreignId('vendor_id')->nullable()->constrained('marketplace_vendors')->onDelete('set null');
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('order_id');
            $table->index('product_id');
        });

        // Marketplace Carts table
        Schema::create('marketplace_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('session_id')->nullable()->index();
            $table->foreignId('vendor_id')->nullable()->constrained('marketplace_vendors')->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('marketplace_products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['user_id', 'product_id']);
            $table->index(['session_id', 'product_id']);
        });

        // Marketplace Reviews table
        Schema::create('marketplace_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('marketplace_products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('marketplace_orders')->onDelete('set null');
            $table->integer('rating');
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('product_id');
            $table->index('user_id');
            $table->unique(['product_id', 'user_id']);
            $table->index(['is_approved', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_reviews');
        Schema::dropIfExists('marketplace_carts');
        Schema::dropIfExists('marketplace_order_items');
        Schema::dropIfExists('marketplace_orders');
        Schema::dropIfExists('marketplace_products');
        Schema::dropIfExists('marketplace_vendors');
    }
};
