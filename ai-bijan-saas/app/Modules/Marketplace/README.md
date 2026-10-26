# B-Marketplace — E-commerce Marketplace Module

## Overview

AI BIJAN Marketplace module provides a full-featured e-commerce platform with product catalog, vendor management, shopping cart, order processing, and product reviews. Supports multi-vendor marketplace with commission tracking, inventory management, and complete order lifecycle.

**Priority:** P2  
**Status:** ✅ Complete  
**Blueprint Reference:** BAB 12, Kumpulan 2 (Jualan, Saluran & Pertumbuhan)

---

## Architecture

```
app/Modules/Marketplace/
├── MarketplaceServiceProvider.php          # Service provider registration
├── config/
│   └── marketplace.php                     # Configuration (categories, statuses, settings)
├── Models/
│   ├── Vendor.php                          # Marketplace vendors/sellers
│   ├── Product.php                         # Product catalog entity
│   ├── Order.php                           # Customer orders
│   ├── OrderItem.php                       # Line items per order
│   ├── Cart.php                            # Shopping cart entries
│   └── Review.php                          # Product reviews & ratings
├── Services/
│   ├── ProductService.php                  # Product CRUD, search, inventory
│   ├── VendorService.php                   # Vendor registration, approval, management
│   ├── OrderService.php                    # Order lifecycle management
│   ├── CartService.php                     # Cart operations & validation
│   └── ReviewService.php                   # Review submission, moderation, ratings
├── Http/Controllers/
│   ├── ProductController.php               # Product API endpoints
│   ├── VendorController.php                # Vendor API endpoints
│   ├── OrderController.php                 # Order API endpoints
│   ├── CartController.php                  # Cart API endpoints
│   └── ReviewController.php                # Review API endpoints
├── database/migrations/
│   └── 2026_10_20_000700_create_marketplace_tables.php
└── routes/
    └── api.php                             # Module-specific routes
```

---

## Database Schema

### Tables

#### `marketplace_vendors`
Marketplace vendors representing sellers on the platform.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| user_id | BIGINT UNSIGNED | FK → users (vendor owner) |
| name | VARCHAR(255) | Vendor display name |
| slug | VARCHAR(255) | URL-friendly unique identifier |
| description | TEXT | Vendor description |
| logo_url | VARCHAR(500) | Logo image URL |
| banner_url | VARCHAR(500) | Banner image URL |
| contact_email | VARCHAR(255) | Contact email |
| contact_phone | VARCHAR(20) | Contact phone number |
| address | TEXT | Physical address |
| city | VARCHAR(100) | City |
| state | VARCHAR(100) | State |
| postal_code | VARCHAR(20) | Postal code |
| country | VARCHAR(100) | Country |
| is_active | BOOLEAN | Active/inactive status |
| is_verified | BOOLEAN | Verified/approved status |
| commission_rate | DECIMAL(5,4) | Platform commission rate (default 5%) |
| rating | DECIMAL(3,2) | Average vendor rating |
| total_sales | INTEGER | Total number of sales |
| total_products | INTEGER | Total number of products |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

**Indexes:** tenant_id, slug, (is_active, is_verified)

#### `marketplace_products`
Product catalog with pricing, inventory, and metadata.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| vendor_id | BIGINT UNSIGNED | FK → marketplace_vendors |
| sku | VARCHAR(100) | Unique stock keeping code |
| name | VARCHAR(255) | Product name |
| slug | VARCHAR(255) | URL-friendly unique identifier |
| description | LONGTEXT | Full product description |
| short_description | TEXT | Brief product summary |
| category | VARCHAR(100) | Product category |
| subcategory | VARCHAR(100) | Sub-category |
| price | DECIMAL(12,2) | Selling price |
| compare_at_price | DECIMAL(12,2) | Original/comparison price |
| cost | DECIMAL(12,2) | Cost price for margin calculation |
| stock_quantity | INTEGER | Available stock |
| low_stock_threshold | INTEGER | Alert threshold (default 5) |
| is_active | BOOLEAN | Active/inactive status |
| is_featured | BOOLEAN | Featured product flag |
| is_digital | BOOLEAN | Digital vs physical product |
| weight | DECIMAL(8,2) | Weight in kg |
| length | DECIMAL(8,2) | Length in cm |
| width | DECIMAL(8,2) | Width in cm |
| height | DECIMAL(8,2) | Height in cm |
| images | JSON | Array of image URLs |
| specifications | JSON | Product specifications |
| tags | JSON | Search tags |
| seo_title | VARCHAR(255) | SEO title |
| seo_description | TEXT | SEO meta description |
| views_count | INTEGER | Number of views |
| sales_count | INTEGER | Number of sales |
| rating | DECIMAL(3,2) | Average rating |
| review_count | INTEGER | Total reviews |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

**Indexes:** tenant_id, slug, (is_active, is_featured), category, vendor_id

#### `marketplace_orders`
Customer orders with status tracking and payment info.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| order_no | VARCHAR(50) | Unique order number (MKT-YYYY-NNNNNN) |
| vendor_id | BIGINT UNSIGNED | FK → marketplace_vendors |
| customer_id | BIGINT UNSIGNED | FK → users (customer) |
| status | VARCHAR(50) | Order status |
| subtotal | DECIMAL(12,2) | Sum of line items before tax/discount |
| tax_amount | DECIMAL(12,2) | Tax amount |
| shipping_cost | DECIMAL(12,2) | Shipping cost |
| discount_amount | DECIMAL(12,2) | Discount amount |
| total_amount | DECIMAL(12,2) | Final amount due |
| currency | VARCHAR(10) | Currency code (default MYR) |
| payment_method | VARCHAR(50) | Payment method used |
| payment_status | VARCHAR(50) | Payment status |
| shipping_method | VARCHAR(100) | Shipping method |
| shipping_address | TEXT | Delivery address |
| billing_address | TEXT | Billing address |
| notes | TEXT | Customer notes |
| internal_notes | TEXT | Internal notes |
| tracking_number | VARCHAR(255) | Shipping tracking number |
| shipped_at | TIMESTAMP | Shipment timestamp |
| delivered_at | TIMESTAMP | Delivery timestamp |
| cancelled_at | TIMESTAMP | Cancellation timestamp |
| cancellation_reason | VARCHAR(255) | Reason for cancellation |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

**Indexes:** tenant_id, order_no, status, payment_status, vendor_id, customer_id

#### `marketplace_order_items`
Line items within an order.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| order_id | BIGINT UNSIGNED | FK → marketplace_orders |
| product_id | BIGINT UNSIGNED | FK → marketplace_products |
| vendor_id | BIGINT UNSIGNED | FK → marketplace_vendors |
| product_name | VARCHAR(255) | Product name at time of order |
| product_sku | VARCHAR(100) | SKU at time of order |
| quantity | INTEGER | Quantity ordered |
| unit_price | DECIMAL(12,2) | Price per unit |
| subtotal | DECIMAL(12,2) | Line subtotal |
| tax_amount | DECIMAL(12,2) | Tax for this item |
| discount_amount | DECIMAL(12,2) | Discount for this item |
| total | DECIMAL(12,2) | Line total |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

**Indexes:** tenant_id, order_id, product_id

#### `marketplace_carts`
Shopping cart entries for users or sessions.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| user_id | BIGINT UNSIGNED | FK → users (nullable for guests) |
| session_id | VARCHAR(255) | Session ID for guest carts |
| vendor_id | BIGINT UNSIGNED | FK → marketplace_vendors |
| product_id | BIGINT UNSIGNED | FK → marketplace_products |
| quantity | INTEGER | Item quantity |
| unit_price | DECIMAL(12,2) | Price at time of adding |
| subtotal | DECIMAL(12,2) | Line subtotal |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

**Indexes:** tenant_id, (user_id, product_id), (session_id, product_id)

#### `marketplace_reviews`
Product reviews and ratings from customers.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| tenant_id | BIGINT UNSIGNED | Multi-tenant isolation |
| product_id | BIGINT UNSIGNED | FK → marketplace_products |
| user_id | BIGINT UNSIGNED | FK → users (reviewer) |
| order_id | BIGINT UNSIGNED | FK → marketplace_orders (for verification) |
| rating | INTEGER | Rating (1-5) |
| title | VARCHAR(255) | Review title |
| comment | TEXT | Review text |
| images | JSON | Review images |
| is_verified_purchase | BOOLEAN | Verified purchase flag |
| is_approved | BOOLEAN | Moderation status |
| helpful_count | INTEGER | Helpful votes |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

**Indexes:** tenant_id, product_id, user_id, (is_approved, created_at)  
**Unique:** (product_id, user_id) — one review per user per product

---

## API Endpoints

### Products

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/marketplace/products` | marketplace.view | List all products |
| GET | `/api/v1/marketplace/products/featured` | marketplace.view | Get featured products |
| GET | `/api/v1/marketplace/products/search` | marketplace.view | Search products |
| GET | `/api/v1/marketplace/products/low-stock` | marketplace.view | Get low stock products |
| GET | `/api/v1/marketplace/products/{id}` | marketplace.view | Get single product |
| POST | `/api/v1/marketplace/products` | marketplace.manage | Create product |
| PUT | `/api/v1/marketplace/products/{id}` | marketplace.manage | Update product |
| DELETE | `/api/v1/marketplace/products/{id}` | marketplace.manage | Delete product |
| POST | `/api/v1/marketplace/products/{id}/stock` | marketplace.manage | Update stock |
| POST | `/api/v1/marketplace/products/bulk-status` | marketplace.manage | Bulk update status |

### Vendors

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/marketplace/vendors` | marketplace.view | List all vendors |
| GET | `/api/v1/marketplace/vendors/top` | marketplace.view | Get top vendors |
| GET | `/api/v1/marketplace/vendors/{id}` | marketplace.view | Get single vendor |
| POST | `/api/v1/marketplace/vendors` | marketplace.manage | Create vendor |
| PUT | `/api/v1/marketplace/vendors/{id}` | marketplace.manage | Update vendor |
| DELETE | `/api/v1/marketplace/vendors/{id}` | marketplace.manage | Delete vendor |
| POST | `/api/v1/marketplace/vendors/{id}/approve` | marketplace.manage | Approve vendor |
| POST | `/api/v1/marketplace/vendors/{id}/reject` | marketplace.manage | Reject vendor |
| POST | `/api/v1/marketplace/vendors/{id}/toggle` | marketplace.manage | Toggle active status |

### Orders

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/marketplace/orders` | marketplace.view | List all orders |
| GET | `/api/v1/marketplace/orders/statistics` | marketplace.view | Get order statistics |
| GET | `/api/v1/marketplace/orders/{id}` | marketplace.view | Get single order |
| POST | `/api/v1/marketplace/orders` | marketplace.manage | Create order from cart |
| POST | `/api/v1/marketplace/orders/{id}/status` | marketplace.manage | Update order status |
| POST | `/api/v1/marketplace/orders/{id}/payment` | marketplace.manage | Confirm payment |
| POST | `/api/v1/marketplace/orders/{id}/cancel` | marketplace.manage | Cancel order |
| POST | `/api/v1/marketplace/orders/{id}/refund` | marketplace.manage | Refund order |
| POST | `/api/v1/marketplace/orders/{id}/tracking` | marketplace.manage | Update tracking |

### Cart

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/marketplace/cart` | marketplace.view | Get cart items |
| GET | `/api/v1/marketplace/cart/totals` | marketplace.view | Get cart totals |
| GET | `/api/v1/marketplace/cart/validate` | marketplace.view | Validate cart |
| POST | `/api/v1/marketplace/cart` | marketplace.manage | Add item to cart |
| PUT | `/api/v1/marketplace/cart/{id}` | marketplace.manage | Update cart item |
| DELETE | `/api/v1/marketplace/cart/{id}` | marketplace.manage | Remove cart item |
| DELETE | `/api/v1/marketplace/cart` | marketplace.manage | Clear cart |

### Reviews

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/marketplace/reviews/product/{productId}` | marketplace.view | List product reviews |
| GET | `/api/v1/marketplace/reviews/product/{productId}/statistics` | marketplace.view | Get review statistics |
| GET | `/api/v1/marketplace/reviews/most-reviewed` | marketplace.view | Get most reviewed products |
| GET | `/api/v1/marketplace/reviews/pending` | marketplace.manage | Get pending reviews |
| POST | `/api/v1/marketplace/reviews` | marketplace.manage | Create review |
| PUT | `/api/v1/marketplace/reviews/{id}` | marketplace.manage | Update review |
| DELETE | `/api/v1/marketplace/reviews/{id}` | marketplace.manage | Delete review |
| POST | `/api/v1/marketplace/reviews/{id}/approve` | marketplace.manage | Approve review |
| POST | `/api/v1/marketplace/reviews/{id}/reject` | marketplace.manage | Reject review |
| POST | `/api/v1/marketplace/reviews/{id}/helpful` | marketplace.manage | Mark as helpful |
| POST | `/api/v1/marketplace/reviews/bulk-approve` | marketplace.manage | Bulk approve reviews |
| POST | `/api/v1/marketplace/reviews/bulk-reject` | marketplace.manage | Bulk reject reviews |

---

## Configuration

### Config File: `config/marketplace.php`

```php
// Product categories
'product_categories' => [
    'electronics' => 'Elektronik',
    'fashion' => 'Fesyen',
    'home_living' => 'Rumah & Hidup',
    'health_beauty' => 'Kesihatan & Kecantikan',
    'food_beverage' => 'Makanan & Minuman',
    // ... more categories
],

// Order statuses
'order_statuses' => [
    'draft' => 'Rangka',
    'pending' => 'Menunggu',
    'confirmed' => 'Disahkan',
    'processing' => 'Diproses',
    'shipped' => 'Dihantar',
    'delivered' => 'Diterima',
    'cancelled' => 'Dibatalkan',
    'refunded' => 'Dipulangkan',
],

// Commission rates
'commission' => [
    'default_rate' => 0.05,      // 5% platform commission
    'min_rate' => 0.02,          // Minimum 2%
    'max_rate' => 0.15,          // Maximum 15%
    'vendor_tiers' => [
        ['min_sales' => 0, 'rate' => 0.05],
        ['min_sales' => 10000, 'rate' => 0.04],
        ['min_sales' => 50000, 'rate' => 0.03],
        ['min_sales' => 100000, 'rate' => 0.02],
    ],
],

// Cart settings
'cart' => [
    'max_items' => 100,
    'save_duration_days' => 30,
    'merge_guest_on_login' => true,
],

// Review settings
'reviews' => [
    'verified_only' => false,
    'moderation_required' => true,
    'min_rating' => 1,
    'max_rating' => 5,
],
```

---

## Status Values

### Order Statuses
- `draft` — Rangka (Draft)
- `pending` — Menunggu (Pending)
- `confirmed` — Disahkan (Confirmed)
- `processing` — Diproses (Processing)
- `shipped` — Dihantar (Shipped)
- `delivered` — Diterima (Delivered)
- `cancelled` — Dibatalkan (Cancelled)
- `refunded` — Dipulangkan (Refunded)
- `partially_refunded` — Sebahagian Dipulangkan (Partially Refunded)

### Payment Statuses
- `unpaid` — Belum Bayar
- `paid` — Sudah Bayar
- `refunded` — Dipulangkan
- `partially_refunded` — Sebahagian Dipulangkan

### Product Categories
- `electronics` — Elektronik
- `fashion` — Fesyen
- `home_living` — Rumah & Hidup
- `health_beauty` — Kesihatan & Kecantikan
- `food_beverage` — Makanan & Minuman
- `sports` — Sukan & Hobi
- `toys` — Mainan & Kanak-kanak
- `automotive` — Automotif
- `books` — Buku & Alat Tulis
- `other` — Lain-lain

---

## Numbering Formats

- **Order Number:** `MKT-YYYY-NNNNNN` (e.g., MKT-2026-000001)
- **Product SKU:** `MKT-PROD-XXXXXXXX` (auto-generated if not provided)
- **Vendor Slug:** Auto-generated from vendor name (URL-friendly)

---

## Service Layer

### ProductService
- `list()` — List products with filters and pagination
- `find()` — Get single product
- `create()` — Create new product with auto-SKU
- `update()` — Update existing product
- `delete()` — Delete product
- `updateStock()` — Update stock (set/add/remove operations)
- `recordView()` — Track product view
- `recordSale()` — Track product sale
- `getFeatured()` — Get featured products
- `search()` — Search across products
- `bulkUpdateStatus()` — Bulk status update
- `getLowStockProducts()` — Get low stock alerts

### VendorService
- `list()` — List vendors with filters
- `find()` — Get single vendor
- `create()` — Create new vendor
- `update()` — Update vendor details
- `delete()` — Delete vendor and products
- `approve()` — Approve vendor application
- `reject()` — Reject vendor application
- `toggleActive()` — Toggle vendor status
- `updateRating()` — Recalculate vendor rating
- `updateProductCount()` — Update product count
- `getTopVendors()` — Get top vendors by sales

### OrderService
- `list()` — List orders with filters
- `find()` — Get single order with items
- `createFromCart()` — Create order from cart items
- `updateStatus()` — Transition order status
- `confirmPayment()` — Confirm payment
- `cancel()` — Cancel order (restore stock)
- `refund()` — Process refund
- `updateTracking()` — Set tracking number
- `getStatistics()` — Get order statistics
- `processPendingOrders()` — Auto-process pending orders

### CartService
- `getForUser()` — Get user cart items
- `getForSession()` — Get guest cart items
- `addItem()` — Add item to cart (with merge logic)
- `addGuestItem()` — Add guest cart item
- `updateQuantity()` — Update cart item quantity
- `removeItem()` — Remove cart item
- `clearCart()` — Clear entire cart
- `mergeGuestCart()` — Merge guest cart on login
- `getTotals()` — Calculate cart totals
- `validateCart()` — Validate cart before checkout
- `expireOldCartItems()` — Clean up old carts

### ReviewService
- `list()` — List product reviews
- `listByUser()` — List user's reviews
- `find()` — Get single review
- `create()` — Create new review
- `update()` — Update review
- `delete()` — Delete review
- `approve()` — Approve review (moderation)
- `reject()` — Reject review (moderation)
- `markHelpful()` — Mark review as helpful
- `updateProductRating()` — Recalculate product rating
- `getStatistics()` — Get review statistics
- `hasReviewed()` — Check if user already reviewed
- `bulkApprove()` — Bulk approve reviews
- `bulkReject()` — Bulk reject reviews

---

## Integration Points

### B-Inventory (Stock Management)
- Stock deduction on order creation
- Stock restoration on order cancellation
- Low stock alerts via ProductService
- Inventory ledger integration

### B-Finance (Financial Management)
- Revenue recording per order
- Commission calculation per vendor
- Payout tracking for vendors
- Tax reporting

### B-CRM (Customer Data)
- Customer data sharing
- Purchase history tracking
- Customer segmentation based on orders

### B-Payroll (Vendor Payouts)
- Vendor payout processing
- Commission deductions
- Payment scheduling

---

## Role Permissions

| Role | Permissions |
|------|------------|
| **tenant_owner** | * (all permissions) |
| **tenant_admin** | marketplace.view, marketplace.manage |
| **finance_manager** | marketplace.view |
| **hr_manager** | marketplace.view |
| **cashier** | — |
| **staff** | — |

---

## Usage Examples

### Creating a Product
```php
$productService = app(ProductService::class);
$product = $productService->create([
    'tenant_id' => 1,
    'vendor_id' => 5,
    'name' => 'Baju Kurung Modern',
    'category' => 'fashion',
    'price' => 149.90,
    'cost' => 60.00,
    'stock_quantity' => 100,
    'is_active' => true,
]);
// SKU auto-generated: MKT-PROD-A1B2C3D4
```

### Processing an Order
```php
$orderService = app(OrderService::class);
$order = $orderService->createFromCart(
    tenantId: 1,
    userId: 42,
    sessionId: 'abc123',
    cartItems: [...],
    orderData: [
        'shipping_method' => 'standard',
        'shipping_address' => '123 Jalan Example, KL',
        'payment_method' => 'bank_transfer',
    ]
);
// Order no auto-generated: MKT-2026-000001
```

### Updating Order Status
```php
$orderService = app(OrderService::class);
$order = $orderService->find(1);
$order = $orderService->updateStatus($order, 'shipped');
// Sets shipped_at timestamp automatically
```

### Adding to Cart
```php
$cartService = app(CartService::class);
$cartItem = $cartService->addItem(
    tenantId: 1,
    userId: 42,
    productId: 15,
    quantity: 2
);
// Merges with existing cart item if present
```

### Submitting a Review
```php
$reviewService = app(ReviewService::class);
$review = $reviewService->create([
    'tenant_id' => 1,
    'product_id' => 15,
    'user_id' => 42,
    'order_id' => 100,
    'rating' => 5,
    'title' => 'Excellent product!',
    'comment' => 'Very satisfied with the quality.',
    'is_verified_purchase' => true,
]);
```

---

## Migration

Run migrations:
```bash
php artisan migrate
```

The migration creates 6 tables:
1. `marketplace_vendors`
2. `marketplace_products`
3. `marketplace_orders`
4. `marketplace_order_items`
5. `marketplace_carts`
6. `marketplace_reviews`

---

## Testing

Run module tests:
```bash
php artisan test --filter=Marketplace
```

---

## Notes

- All models use multi-tenant isolation via `tenant_id`
- Cart supports both authenticated users and guest sessions
- Reviews require moderation by default (configurable)
- Commission rates are tiered based on vendor sales volume
- Stock is automatically managed during order lifecycle
- Product ratings are recalculated after each review change
- Order status transitions are validated (cannot skip stages)
- Guest cart can be merged into user cart on login
