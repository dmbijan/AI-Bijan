<?php

return [
    // Available themes
    'themes' => [
        'default' => [
            'name' => 'Default',
            'description' => 'Clean modern theme for general business websites',
            'preview_url' => '/themes/default/preview',
            'settings' => [
                'primary_color' => '#1a73e8',
                'secondary_color' => '#5f6368',
                'font_family' => 'Inter, sans-serif',
                'header_style' => 'sticky',
                'footer_style' => 'standard',
            ],
        ],
        'minimal' => [
            'name' => 'Minimal',
            'description' => 'Simple minimalist design for portfolios',
            'preview_url' => '/themes/minimal/preview',
            'settings' => [
                'primary_color' => '#000000',
                'secondary_color' => '#666666',
                'font_family' => 'Roboto, sans-serif',
                'header_style' => 'transparent',
                'footer_style' => 'minimal',
            ],
        ],
        'storefront' => [
            'name' => 'Storefront',
            'description' => 'E-commerce focused theme for online stores',
            'preview_url' => '/themes/storefront/preview',
            'settings' => [
                'primary_color' => '#ff6b35',
                'secondary_color' => '#2d2d2d',
                'font_family' => 'Poppins, sans-serif',
                'header_style' => 'mega-menu',
                'footer_style' => 'ecommerce',
            ],
        ],
        'blog' => [
            'name' => 'Blog',
            'description' => 'Content-focused theme for blogs and magazines',
            'preview_url' => '/themes/blog/preview',
            'settings' => [
                'primary_color' => '#e91e63',
                'secondary_color' => '#424242',
                'font_family' => 'Merriweather, serif',
                'header_style' => 'centered',
                'footer_style' => 'blog',
            ],
        ],
        'corporate' => [
            'name' => 'Corporate',
            'description' => 'Professional theme for corporate websites',
            'preview_url' => '/themes/corporate/preview',
            'settings' => [
                'primary_color' => '#1565c0',
                'secondary_color' => '#37474f',
                'font_family' => 'Open Sans, sans-serif',
                'header_style' => 'classic',
                'footer_style' => 'corporate',
            ],
        ],
    ],

    // Page templates
    'page_templates' => [
        'default' => 'Default Template',
        'full_width' => 'Full Width',
        'sidebar_left' => 'Sidebar Left',
        'sidebar_right' => 'Sidebar Right',
        'landing' => 'Landing Page',
        'coming_soon' => 'Coming Soon',
        'maintenance' => 'Maintenance Mode',
    ],

    // Post statuses
    'post_statuses' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
        'pending_review' => 'Pending Review',
    ],

    // Section types available
    'section_types' => [
        'hero' => 'Hero Section',
        'features' => 'Features Grid',
        'testimonials' => 'Testimonials Carousel',
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
        'blog_posts' => 'Latest Blog Posts',
    ],

    // SEO settings
    'seo' => [
        'meta_title_max_length' => 60,
        'meta_description_max_length' => 160,
        'slug_separator' => '-',
        'auto_generate_slug' => true,
    ],

    // Publishing settings
    'publishing' => [
        'auto_publish_on_date' => true,
        'require_approval' => false,
        'version_history_enabled' => true,
        'max_versions' => 50,
    ],

    // Domain settings
    'domains' => [
        'subdomain_pattern' => '{site}.aibijan.com',
        'ssl_required' => true,
        'default_www_redirect' => true,
    ],

    // Cache settings
    'cache' => [
        'page_cache_duration_minutes' => 60,
        'cdn_enabled' => false,
        'lazy_load_images' => true,
    ],
];
