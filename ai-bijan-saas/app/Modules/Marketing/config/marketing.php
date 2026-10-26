<?php

return [
    // Campaign types
    'campaign_types' => [
        'email' => 'Email Campaign',
        'sms' => 'SMS Campaign',
        'whatsapp' => 'WhatsApp Campaign',
        'push' => 'Push Notification',
        'social' => 'Social Media Campaign',
        'multi_channel' => 'Multi-Channel Campaign',
    ],

    // Campaign statuses
    'campaign_statuses' => [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'running' => 'Running',
        'paused' => 'Paused',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    // Segment types
    'segment_types' => [
        'static' => 'Static Segment',
        'dynamic' => 'Dynamic Segment',
    ],

    // Voucher types
    'voucher_types' => [
        'percentage' => 'Percentage Discount',
        'fixed' => 'Fixed Amount Discount',
        'free_shipping' => 'Free Shipping',
        'buy_one_get_one' => 'Buy One Get One Free',
        'gift' => 'Free Gift',
    ],

    // Voucher statuses
    'voucher_statuses' => [
        'draft' => 'Draft',
        'active' => 'Active',
        'expired' => 'Expired',
        'paused' => 'Paused',
    ],

    // Contact sources
    'contact_sources' => [
        'website_form' => 'Website Form',
        'import' => 'CSV Import',
        'api' => 'API',
        'manual' => 'Manual Entry',
        'crm' => 'CRM Integration',
        'ecommerce' => 'E-commerce',
        'event' => 'Event Registration',
        'social_media' => 'Social Media',
    ],

    // Contact tags
    'contact_tags' => [
        'lead' => 'Lead',
        'customer' => 'Customer',
        'vip' => 'VIP Customer',
        'inactive' => 'Inactive',
        'churned' => 'Churned',
        'new' => 'New Contact',
    ],

    // Deal stages
    'deal_stages' => [
        'prospect' => 'Prospect',
        'qualified' => 'Qualified',
        'proposal' => 'Proposal Sent',
        'negotiation' => 'Negotiation',
        'closed_won' => 'Closed Won',
        'closed_lost' => 'Closed Lost',
    ],

    // Sequence triggers
    'sequence_triggers' => [
        'form_submission' => 'Form Submission',
        'page_visit' => 'Page Visit',
        'email_open' => 'Email Opened',
        'email_click' => 'Email Link Clicked',
        'signup' => 'User Signup',
        'purchase' => 'Purchase Made',
        'abandoned_cart' => 'Abandoned Cart',
        'custom' => 'Custom Event',
    ],

    // Workflow triggers
    'workflow_triggers' => [
        'schedule' => 'Scheduled',
        'webhook' => 'Webhook Received',
        'form_submission' => 'Form Submission',
        'deal_stage_change' => 'Deal Stage Changed',
        'contact_property_change' => 'Contact Property Changed',
        'date_based' => 'Date Based (e.g., Birthday)',
    ],

    // Workflow actions
    'workflow_actions' => [
        'send_email' => 'Send Email',
        'send_sms' => 'Send SMS',
        'send_whatsapp' => 'Send WhatsApp',
        'update_contact' => 'Update Contact Properties',
        'create_task' => 'Create Task',
        'create_deal' => 'Create Deal',
        'add_to_segment' => 'Add to Segment',
        'remove_from_segment' => 'Remove from Segment',
        'delay' => 'Delay',
    ],

    // Lead scoring categories
    'lead_score_categories' => [
        'demographic' => 'Demographic Fit',
        'firmographic' => 'Firmographic Fit',
        'engagement' => 'Engagement Score',
        'behavioral' => 'Behavioral Score',
        'transactional' => 'Transactional Score',
    ],

    // Ad platforms
    'ad_platforms' => [
        'google_ads' => 'Google Ads',
        'facebook_ads' => 'Facebook/Meta Ads',
        'instagram_ads' => 'Instagram Ads',
        'tiktok_ads' => 'TikTok Ads',
        'linkedin_ads' => 'LinkedIn Ads',
        'twitter_ads' => 'Twitter/X Ads',
        'youtube_ads' => 'YouTube Ads',
    ],

    // Email templates
    'email_templates' => [
        'welcome' => 'Welcome Email',
        'newsletter' => 'Newsletter',
        'promotional' => 'Promotional',
        'abandoned_cart' => 'Abandoned Cart Reminder',
        'follow_up' => 'Follow Up',
        're_engagement' => 'Re-engagement',
        'birthday' => 'Birthday Greeting',
        'announcement' => 'Product/Service Announcement',
    ],

    // Analytics settings
    'analytics' => [
        'attribution_models' => ['first_touch', 'last_touch', 'linear', 'time_decay', 'position_based'],
        'default_attribution_model' => 'last_touch',
        'cookie_consent_required' => true,
        'data_retention_days' => 730, // 2 years
    ],

    // Compliance settings
    'compliance' => [
        'gdpr_enabled' => true,
        'consent_required' => true,
        'data_portability' => true,
        'right_to_be_forgotten' => true,
        'opt_out_allowed' => true,
    ],

    // Rate limiting
    'rate_limits' => [
        'emails_per_hour' => 1000,
        'sms_per_hour' => 500,
        'whatsapp_per_hour' => 500,
        'api_calls_per_minute' => 100,
    ],
];
