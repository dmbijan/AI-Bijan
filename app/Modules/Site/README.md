# B-Site — Website Builder (Shopify Replacement)

## Ringkasan

B-Site adalah modul pembina laman web lengkap yang berfungsi sebagai alternatif Shopify untuk ekosistem AI-BIJAN. Modul ini membolehkan setiap tenant membuat dan menguruskan laman web mereka sendiri dengan ciri-ciri:

- **Multi-site Management** — Setiap tenant boleh mempunyai beberapa laman web
- **Page Builder** — Pembinaan halaman dengan template dan sections yang boleh disesuaikan
- **Theme System** — Sistem tema dengan pelbagai pilihan dan kustomisasi
- **Blog/CMS** — Pengurusan kandungan blog dengan publish scheduling
- **Contact Forms** — Form builder dengan submission tracking
- **Domain Management** — Pengurusan domain custom dengan SSL support
- **Analytics Dashboard** — Statistik trafik, page views, dan conversion tracking

## Ciri-ciri Utama

### 1. Site Management
- Multiple websites per tenant
- Custom domain support (primary + secondary domains)
- Maintenance mode & coming soon mode
- SEO metadata per site
- Analytics integration (Google Analytics, GTM, Facebook Pixel)
- Multi-language support

### 2. Page Builder
- Visual page builder dengan drag-and-drop sections
- Template system (default, full_width, sidebar_left, sidebar_right, landing, coming_soon, maintenance)
- Section types: hero, features, testimonials, gallery, pricing, team, contact, cta, faq, stats, video, newsletter, products, blog_posts
- SEO optimization per page
- Publish scheduling

### 3. Theme System
- Pre-built themes: default, minimal, storefront, blog, corporate
- Theme customization dengan settings panel
- Active/default theme management
- Theme replication untuk custom modifications

### 4. Blog/CMS
- Blog post creation & management
- Draft, published, scheduled statuses
- Tags & categories
- Comments with moderation
- View tracking
- RSS feed ready

### 5. Contact Forms
- Form builder dengan dynamic fields
- Spam protection (reCAPTCHA, honeypot)
- Email notifications
- Webhook support
- Submission tracking

### 6. Domain Management
- Primary & secondary domains
- SSL certificate management
- DNS record verification
- Domain forwarding
- Verification code generation

### 7. Analytics
- Page view statistics
- Traffic analysis
- Top pages/posts by views
- Form submission tracking
- Conversion rate monitoring
- Visitor location data
- Referrer tracking

## Struktur Fail

```
app/Modules/Site/
├── SiteServiceProvider.php           # Service provider dengan 7 service bindings ✅
├── config/site.php                   # Themes, templates, statuses, settings ✅
├── Models/ (9 files)
│   ├── Site.php                      # Site entity dengan slug, domain, SEO ✅
│   ├── Page.php                      # Pages dengan templates, sections ✅
│   ├── Theme.php                     # Theme configuration ✅
│   ├── Section.php                   # Reusable page sections/blocks ✅
│   ├── Post.php                      # Blog posts dengan publish scheduling ✅
│   ├── Form.php                      # Contact forms configuration ✅
│   ├── FormSubmission.php            # Form submission records ✅
│   ├── Domain.php                    # Custom domain management ✅
│   └── PostComment.php               # Blog comments dengan moderation ✅
├── Services/ (7 files)
│   ├── SiteService.php               # Site CRUD, live/deactivate, settings ✅
│   ├── PageService.php               # Page CRUD, template, section ordering ✅
│   ├── ThemeService.php              # Theme installation, activation ✅
│   ├── PostService.php               # Blog post CRUD, publish scheduling ✅
│   ├── FormService.php               # Form creation, submissions ✅
│   ├── DomainService.php             # Domain assignment, SSL, DNS checks ✅
│   └── AnalyticsService.php          # Page views, traffic, conversions ✅
├── Http/Controllers/ (7 files)
│   ├── SiteController.php            # Site management endpoints ✅
│   ├── PageController.php            # Page CRUD, preview, publish ✅
│   ├── ThemeController.php           # Theme selection & customization ✅
│   ├── PostController.php            # Blog post management ✅
│   ├── FormController.php            # Form management & submissions ✅
│   ├── DomainController.php          # Domain management & SSL ✅
│   └── AnalyticsController.php       # Analytics dashboard data ✅
├── database/migrations/
│   └── 2026_10_20_000800_create_site_tables.php  # 8 tables ✅
├── routes/api.php                    # Module-specific routes ✅
└── README.md                         # Comprehensive documentation ✅
```

## Database Schema

### Tables Created

1. **sites** — Laman web utama
   - tenant_id, name, slug, description
   - logo_url, favicon_url, primary_domain
   - secondary_domains (JSON)
   - theme, status, is_live
   - maintenance_mode, coming_soon_mode
   - SEO fields (title, description, keywords, og_image)
   - Analytics IDs (GA, GTM, FB Pixel)
   - Custom head/body code
   - language, timezone

2. **site_pages** — Halaman laman web
   - tenant_id, site_id, title, slug
   - template, status, content
   - sections (JSON), meta fields
   - author_id, published_at
   - order (untuk drag-drop)

3. **site_themes** — Tema laman web
   - tenant_id, site_id, name, slug
   - version, author, description
   - preview_image, settings (JSON)
   - is_active, is_default

4. **site_sections** — Sections dalam halaman
   - tenant_id, page_id, type, name
   - settings, content, styles (JSON)
   - order, is_visible

5. **site_posts** — Blog posts
   - tenant_id, site_id, title, slug
   - excerpt, content, featured_image
   - status, author_id, published_at
   - meta fields, tags (JSON)
   - views_count

6. **site_post_comments** — Blog comments
   - tenant_id, post_id, parent_id (replies)
   - user_id, name, email, content
   - status, is_approved

7. **site_forms** — Contact forms
   - tenant_id, site_id, name, slug
   - title, description, fields (JSON)
   - submit_action, success_message
   - is_active, spam_protection (JSON)
   - notification_email (JSON)

8. **site_form_submissions** — Form submissions
   - tenant_id, form_id, data (JSON)
   - ip_address, user_agent
   - is_spam

9. **site_domains** — Custom domains
   - tenant_id, site_id, domain
   - type, is_primary
   - ssl_enabled, ssl_status
   - redirect_to, verified
   - verification_code, dns_records (JSON)

## API Endpoints

### Sites
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/site/sites` | site.view | List all sites |
| POST | `/api/v1/site/sites` | site.manage | Create new site |
| GET | `/api/v1/site/sites/{id}` | site.view | Get site details |
| PUT | `/api/v1/site/sites/{id}` | site.manage | Update site |
| DELETE | `/api/v1/site/sites/{id}` | site.manage | Delete site |
| POST | `/api/v1/site/sites/{id}/toggle-live` | site.manage | Toggle live status |
| POST | `/api/v1/site/sites/{id}/toggle-maintenance` | site.manage | Toggle maintenance mode |
| POST | `/api/v1/site/sites/{id}/set-primary-domain` | site.manage | Set primary domain |
| GET | `/api/v1/site/sites/slug/{slug}` | site.view | Get site by slug |

### Pages
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/site/sites/{siteId}/pages` | site.view | List pages |
| POST | `/api/v1/site/sites/{siteId}/pages` | site.manage | Create page |
| GET | `/api/v1/site/pages/{id}` | site.view | Get page |
| PUT | `/api/v1/site/pages/{id}` | site.manage | Update page |
| DELETE | `/api/v1/site/pages/{id}` | site.manage | Delete page |
| POST | `/api/v1/site/pages/{id}/publish` | site.manage | Publish page |
| POST | `/api/v1/site/pages/{id}/unpublish` | site.manage | Unpublish page |
| POST | `/api/v1/site/pages/{id}/duplicate` | site.manage | Duplicate page |
| GET | `/api/v1/site/sites/{siteId}/pages/slug/{slug}` | site.view | Get page by slug |

### Themes
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/site/themes` | site.view | List themes |
| POST | `/api/v1/site/themes` | site.manage | Create theme |
| GET | `/api/v1/site/themes/{id}` | site.view | Get theme |
| PUT | `/api/v1/site/themes/{id}` | site.manage | Update theme |
| DELETE | `/api/v1/site/themes/{id}` | site.manage | Delete theme |
| POST | `/api/v1/site/themes/{id}/activate` | site.manage | Activate theme |
| POST | `/api/v1/site/themes/{id}/apply-settings` | site.manage | Apply theme settings |
| GET | `/api/v1/site/sites/{siteId}/themes/active` | site.view | Get active theme |

### Posts (Blog)
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/site/sites/{siteId}/posts` | site.view | List posts |
| POST | `/api/v1/site/sites/{siteId}/posts` | site.manage | Create post |
| GET | `/api/v1/site/posts/{id}` | site.view | Get post |
| PUT | `/api/v1/site/posts/{id}` | site.manage | Update post |
| DELETE | `/api/v1/site/posts/{id}` | site.manage | Delete post |
| POST | `/api/v1/site/posts/{id}/publish` | site.manage | Publish post |
| POST | `/api/v1/site/posts/{id}/schedule` | site.manage | Schedule post |
| GET | `/api/v1/site/sites/{siteId}/posts/recent` | site.view | Recent posts |
| GET | `/api/v1/site/sites/{siteId}/posts/slug/{slug}` | site.view | Get post by slug |

### Forms
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/site/sites/{siteId}/forms` | site.view | List forms |
| POST | `/api/v1/site/sites/{siteId}/forms` | site.manage | Create form |
| GET | `/api/v1/site/forms/{id}` | site.view | Get form |
| PUT | `/api/v1/site/forms/{id}` | site.manage | Update form |
| DELETE | `/api/v1/site/forms/{id}` | site.manage | Delete form |
| POST | `/api/v1/site/forms/{id}/toggle-active` | site.manage | Toggle active |
| GET | `/api/v1/site/forms/{id}/submissions` | site.view | Get submissions |
| POST | `/api/v1/site/forms/{id}/submit` | none | Public form submission |

### Domains
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/site/domains` | site.view | List domains |
| POST | `/api/v1/site/domains` | site.manage | Add domain |
| GET | `/api/v1/site/domains/{id}` | site.view | Get domain |
| PUT | `/api/v1/site/domains/{id}` | site.manage | Update domain |
| DELETE | `/api/v1/site/domains/{id}` | site.manage | Delete domain |
| POST | `/api/v1/site/domains/{id}/set-primary` | site.manage | Set as primary |
| POST | `/api/v1/site/domains/{id}/enable-ssl` | site.manage | Enable SSL |
| POST | `/api/v1/site/domains/{id}/verify-ssl` | site.manage | Verify SSL |
| POST | `/api/v1/site/domains/{id}/verify` | site.manage | Verify ownership |
| POST | `/api/v1/site/domains/{id}/generate-verification-code` | site.manage | Generate verification code |
| POST | `/api/v1/site/domains/{id}/check-dns` | site.manage | Check DNS records |
| GET | `/api/v1/site/domains/sites/{siteId}` | site.view | Get site domains |

### Analytics
| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/api/v1/site/sites/{siteId}/analytics/page-views` | site.reports | Page view stats |
| GET | `/api/v1/site/sites/{siteId}/analytics/traffic-stats` | site.reports | Traffic stats |
| GET | `/api/v1/site/sites/{siteId}/analytics/form-stats` | site.reports | Form stats |
| GET | `/api/v1/site/sites/{siteId}/analytics/top-pages` | site.reports | Top pages |
| GET | `/api/v1/site/sites/{siteId}/analytics/top-posts` | site.reports | Top posts |
| GET | `/api/v1/site/sites/{siteId}/analytics/conversion-rate` | site.reports | Conversion rate |
| GET | `/api/v1/site/sites/{siteId}/analytics/visitor-locations` | site.reports | Visitor locations |
| GET | `/api/v1/site/sites/{siteId}/analytics/referrers` | site.reports | Referrer data |
| GET | `/api/v1/site/sites/{siteId}/analytics/dashboard` | site.reports | Full dashboard |

## Permission Naming Convention

- `site.view` — Lihat laman web, halaman, tema, blog & domain
- `site.manage` — Urus laman web, halaman, tema, blog & domain
- `site.reports` — Akses analitik & laporan laman web

## Role Assignments

| Role | Permissions |
|------|------------|
| tenant_owner | * (all permissions) |
| tenant_admin | site.view, site.manage |
| finance_manager | site.view |
| hr_manager | site.view |
| cashier | site.view |
| staff | site.view |

## Integration Points

### Dengan B-Marketplace
- Produk marketplace boleh di-sync ke laman web
- Product showcase dalam page sections
- Shopping cart integration

### Dengan B-Marketing
- Landing pages untuk campaign
- Form submissions untuk lead capture
- Analytics untuk campaign tracking

### Dengan B-CRM
- Contact form submissions → CRM leads
- Blog engagement tracking
- Customer journey mapping

### Dengan B-Virtual Office
- Agent-facing website portals
- Task management untuk content updates
- Board-based content planning

## Configuration

### config/site.php

```php
return [
    // Available themes
    'themes' => ['default', 'minimal', 'storefront', 'blog', 'corporate'],
    
    // Page templates
    'templates' => [
        'default' => 'Default',
        'full_width' => 'Full Width',
        'sidebar_left' => 'Sidebar Left',
        'sidebar_right' => 'Sidebar Right',
        'landing' => 'Landing Page',
        'coming_soon' => 'Coming Soon',
        'maintenance' => 'Maintenance',
    ],
    
    // Section types
    'sections' => [
        'hero' => 'Hero Banner',
        'features' => 'Features Grid',
        'testimonials' => 'Testimonials',
        'gallery' => 'Image Gallery',
        'pricing' => 'Pricing Table',
        'team' => 'Team Members',
        'contact' => 'Contact Form',
        'cta' => 'Call to Action',
        'faq' => 'FAQ Accordion',
        'stats' => 'Statistics Counter',
        'video' => 'Video Embed',
        'newsletter' => 'Newsletter Signup',
        'products' => 'Product Showcase',
        'blog_posts' => 'Recent Blog Posts',
    ],
    
    // Post statuses
    'post_statuses' => ['draft', 'published', 'scheduled'],
    
    // SEO settings
    'seo' => [
        'max_title_length' => 60,
        'max_description_length' => 160,
        'default_og_image' => null,
    ],
    
    // Publishing settings
    'publishing' => [
        'auto_publish_on_date' => true,
        'require_moderation' => false,
    ],
    
    // Domain settings
    'domains' => [
        'require_ssl' => true,
        'auto_redirect_https' => true,
        'dns_check_interval' => 300, // seconds
    ],
    
    // Cache settings
    'cache' => [
        'page_cache_ttl' => 3600, // seconds
        'analytics_cache_ttl' => 300,
    ],
];
```

## Development Guidelines

### Creating a New Page
```php
$page = PageService::createPage([
    'tenant_id' => $tenantId,
    'site_id' => $siteId,
    'title' => 'About Us',
    'slug' => 'about',
    'template' => 'default',
    'status' => 'draft',
    'content' => '<p>Content here</p>',
    'sections' => [
        ['type' => 'hero', 'settings' => [...]],
        ['type' => 'features', 'settings' => [...]],
    ],
]);
```

### Publishing a Post
```php
$post = PostService::createPost([...]);
PostService::publishPost($post);

// Or schedule for future
PostService::schedulePost($post, '2026-12-01 09:00:00');
```

### Handling Form Submissions
```php
$form = FormService::getFormById($formId, $tenantId);
$submission = FormService::submitForm(
    $form,
    ['name' => 'John', 'email' => 'john@example.com', 'message' => 'Hello'],
    request()->ip(),
    request()->userAgent()
);
```

### Domain SSL Setup
```php
$domain = DomainService::createDomain([
    'tenant_id' => $tenantId,
    'site_id' => $siteId,
    'domain' => 'example.com',
    'is_primary' => true,
]);

DomainService::enableSsl($domain);
// After DNS verification:
DomainService::verifySsl($domain);
```

## Testing

Run tests dengan command:
```bash
php artisan test --filter=SiteModuleTest
```

## Deployment Notes

1. **Migration**: Jalankan migration selepas module diaktifkan
   ```bash
   php artisan migrate
   ```

2. **Cache Clear**: Clear cache selepas deployment
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

3. **Queue Workers**: Analytics dan email notifications memerlukan queue worker
   ```bash
   php artisan queue:work
   ```

4. **SSL Setup**: Pastikan server configured untuk SSL termination
   - Use Let's Encrypt atau commercial SSL certificates
   - Configure HTTPS redirect in web server

5. **DNS Configuration**: 
   - Setup wildcard DNS jika perlu
   - Configure CNAME records untuk subdomains

## Future Enhancements

- [ ] Visual drag-and-drop page builder UI
- [ ] Theme marketplace untuk pre-built themes
- [ ] A/B testing untuk landing pages
- [ ] Multi-language content management
- [ ] E-commerce integration (product pages)
- [ ] Membership & subscription pages
- [ ] Custom CSS/JS per theme
- [ ] Widget system untuk reusable components
- [ ] Import/Export site templates
- [ ] White-label customization options

## Troubleshooting

### Issue: Domain SSL tidak aktif
- Check DNS records (CNAME atau A record)
- Verify SSL certificate status di hosting provider
- Run `DomainService::checkDnsRecords()` untuk diagnose

### Issue: Form submissions tidak diterima
- Check queue workers running
- Verify email configuration
- Check form is_active status

### Issue: Analytics data tidak muncul
- Clear analytics cache
- Check Google Analytics/Facebook Pixel IDs
- Verify page view tracking code in custom_head_code

## License

Bagian dari ekosistem AI-BIJAN SaaS.

---

**Status**: ✅ COMPLETE (10/10)
**Priority**: P0 (High Priority)
**Blueprint Reference**: BAB 53 — Full Website Builder
