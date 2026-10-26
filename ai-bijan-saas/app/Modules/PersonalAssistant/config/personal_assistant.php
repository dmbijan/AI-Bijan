<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 9 Jenis Personal AI Assistant (BAB 17)
    |--------------------------------------------------------------------------
    |
    | Setiap assistant mewakili peranan pengguna berbeza. Ia menggunakan
    | konteks bisnes yang sama tetapi fokus pada tugas berbeza.
    |
    */

    'assistants' => [

        'owner' => [
            'id' => 'pa_owner',
            'name' => 'Owner AI Assistant',
            'role' => 'owner',
            'description' => 'Ringkasan bisnes, cash-flow, lead, outstanding invoices, risiko dan cadangan tindakan untuk pemilik.',
            'capabilities' => [
                'business_summary',
                'cashflow_overview',
                'sales_performance',
                'outstanding_invoices',
                'business_risks',
                'action_recommendations',
                'approval_requests',
            ],
            'focus_modules' => ['finance', 'crm', 'invoicing', 'analytics', 'projects'],
            'default_tone' => 'executive',
            'proactive_alerts' => true,
        ],

        'executive' => [
            'id' => 'pa_executive',
            'name' => 'Executive AI Assistant',
            'role' => 'executive',
            'description' => 'Executive briefing, KPI monitoring, meeting prep dan decision summaries untuk pengurusan kanan.',
            'capabilities' => [
                'executive_briefing',
                'kpi_monitoring',
                'meeting_preparation',
                'decision_summary',
                'department_performance',
                'risk_escalation',
            ],
            'focus_modules' => ['analytics', 'projects', 'hrms', 'finance'],
            'default_tone' => 'executive',
            'proactive_alerts' => true,
        ],

        'sales' => [
            'id' => 'pa_sales',
            'name' => 'Sales AI Assistant',
            'role' => 'sales',
            'description' => 'Lead qualification, CRM updates, follow-up, quotation dan pipeline monitoring.',
            'capabilities' => [
                'lead_qualification',
                'crm_update',
                'follow_up_planning',
                'quotation_draft',
                'pipeline_monitoring',
                'objection_handling',
                'collection_reminder',
            ],
            'focus_modules' => ['crm', 'invoicing', 'omnichannel', 'marketing'],
            'default_tone' => 'persuasive',
            'proactive_alerts' => true,
        ],

        'marketing' => [
            'id' => 'pa_marketing',
            'name' => 'Marketing AI Assistant',
            'role' => 'marketing',
            'description' => 'Campaign planning, audience segmentation, content generation, SEO dan budget monitoring.',
            'capabilities' => [
                'campaign_planning',
                'audience_segmentation',
                'content_generation',
                'seo_planning',
                'marketing_calendar',
                'campaign_measurement',
                'budget_monitoring',
            ],
            'focus_modules' => ['marketing', 'site', 'loyalty', 'analytics'],
            'default_tone' => 'creative',
            'proactive_alerts' => true,
        ],

        'operations' => [
            'id' => 'pa_operations',
            'name' => 'Operations AI Assistant',
            'role' => 'operations',
            'description' => 'Task coordination, workflow monitoring, inventory alerts dan vendor coordination.',
            'capabilities' => [
                'task_coordination',
                'workflow_monitoring',
                'inventory_alerts',
                'vendor_coordination',
                'service_delivery_tracking',
                'sop_assistance',
            ],
            'focus_modules' => ['inventory', 'projects', 'logistics', 'manufacturing'],
            'default_tone' => 'direct',
            'proactive_alerts' => true,
        ],

        'finance' => [
            'id' => 'pa_finance',
            'name' => 'Finance AI Assistant',
            'role' => 'finance',
            'description' => 'Invoice summaries, receivables, cash-flow forecast, budget variance dan reconciliation.',
            'capabilities' => [
                'invoice_summary',
                'receivables_monitoring',
                'expense_categorization',
                'cashflow_forecast',
                'budget_variance',
                'reconciliation_analysis',
                'finance_report',
            ],
            'focus_modules' => ['finance', 'invoicing', 'payroll', 'cogs'],
            'default_tone' => 'precise',
            'proactive_alerts' => true,
            // Sekatan penting: AI tidak boleh muktamadkan jurnal/kewangan
            'restrictions' => [
                'cannot_finalize_journal',
                'cannot_approve_refund',
                'cannot_transfer_money',
            ],
        ],

        'hr' => [
            'id' => 'pa_hr',
            'name' => 'HR AI Assistant',
            'role' => 'hr',
            'description' => 'Onboarding checklist, shift planning, leave workflow dan policy Q&A.',
            'capabilities' => [
                'onboarding_checklist',
                'training_plan',
                'shift_planning',
                'leave_workflow',
                'policy_qa',
                'staffing_summary',
            ],
            'focus_modules' => ['hrms', 'payroll', 'projects'],
            'default_tone' => 'supportive',
            'proactive_alerts' => true,
            // Keputusan pekerjaan berimpak tinggi tidak boleh dibuat oleh AI semata
            'restrictions' => [
                'cannot_make_hiring_decision',
                'cannot_terminate_employee',
            ],
        ],

        'support' => [
            'id' => 'pa_support',
            'name' => 'Customer Service AI Assistant',
            'role' => 'support',
            'description' => 'FAQ answers, ticket classification, draft replies dan escalation.',
            'capabilities' => [
                'faq_answer',
                'order_status_lookup',
                'reply_draft',
                'ticket_classification',
                'case_routing',
                'complaint_escalation',
                'conversation_summary',
            ],
            'focus_modules' => ['helpdesk', 'omnichannel', 'marketplace', 'logistics'],
            'default_tone' => 'friendly',
            'proactive_alerts' => true,
        ],

        'industry' => [
            'id' => 'pa_industry',
            'name' => 'Industry-Specific Assistant',
            'role' => 'industry',
            'description' => 'Assistant khusus mengikut industri (Event Planner, Retail Ops, F&B Ops, Fleet, Salon, Construction, Property, Professional Services).',
            'capabilities' => [
                'industry_workflow',
                'industry_knowledge',
                'industry_kpi',
                'industry_compliance_checklist',
            ],
            'focus_modules' => [], // Ditentukan oleh industry pack
            'default_tone' => 'professional',
            'proactive_alerts' => false,
            'industry_variants' => [
                'event_planner',
                'retail_operations',
                'fnb_operations',
                'fleet_operations',
                'salon_booking',
                'construction_project',
                'property_management',
                'professional_services',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Memory Architecture (BAB 17A)
    |--------------------------------------------------------------------------
    |
    | 6 jenis memori yang dibezakan oleh sistem. Setiap satu mempunyai
    | akses dan polisi berbeza.
    |
    */

    'memory_types' => [
        'personal_preferences' => [
            'label' => 'Personal preferences',
            'storage' => 'database',
            'table' => 'ai_user_preferences',
            'scope' => 'user',
            'examples' => ['Bahasa pilihan', 'Format laporan', 'Tone komunikasi'],
        ],
        'work_context' => [
            'label' => 'Work context',
            'storage' => 'database',
            'table' => 'ai_memory_items',
            'scope' => 'user_with_permission',
            'examples' => ['Tugasan semasa', 'Projek ditugaskan'],
        ],
        'business_profile' => [
            'label' => 'Business profile',
            'storage' => 'database',
            'table' => 'ai_business_profiles',
            'scope' => 'tenant',
            'examples' => ['Produk', 'Harga', 'Sasaran syarikat'],
        ],
        'operational_records' => [
            'label' => 'Operational records',
            'storage' => 'source_module',
            'table' => null, // Dibaca dari modul sumber
            'scope' => 'permission_based',
            'examples' => ['Orders', 'Invoices', 'Tasks'],
        ],
        'conversation_history' => [
            'label' => 'Conversation history',
            'storage' => 'database',
            'table' => 'ai_conversations',
            'scope' => 'user',
            'examples' => ['Perbualan terdahulu'],
        ],
        'approved_knowledge' => [
            'label' => 'Approved knowledge',
            'storage' => 'vector_store',
            'table' => 'qdrant_documents',
            'scope' => 'tenant_classified',
            'examples' => ['SOP', 'Dokumen rasmi', 'Polisi'],
        ],
        'derived_insights' => [
            'label' => 'Derived insights',
            'storage' => 'database',
            'table' => 'ai_derived_insights',
            'scope' => 'data_source_access',
            'examples' => ['Trend jualan', 'Risiko', 'Cadangan'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Memory Rules (BAB 17.4)
    |--------------------------------------------------------------------------
    */

    'memory_rules' => [
        'store_only_purposeful_data' => true,
        'allow_user_review_correction_deletion' => true,
        'never_treat_inference_as_fact' => true,
        'record_source_and_timestamp_for_facts' => true,
        'respect_original_permissions_on_retrieval' => true,
        'never_use_other_tenant_data' => true,
        'never_expose_personal_data_to_unauthorized' => true,
        'distinguish_fact_inference_forecast_recommendation' => true,
        'official_data_from_source_of_truth' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Task Execution States (BAB 17.5)
    |--------------------------------------------------------------------------
    */

    'task_states' => [
        'draft',
        'planned',
        'awaiting_approval',
        'queued',
        'running',
        'completed',
        'partially_completed',
        'failed',
        'cancelled',
    ],

    /*
    |--------------------------------------------------------------------------
    | Proactive Alerts (BAB 17.6)
    |--------------------------------------------------------------------------
    */

    'proactive_alerts' => [
        'lead_not_contacted' => [
            'label' => 'Lead penting tidak dihubungi',
            'default_threshold_days' => 3,
            'priority' => 'high',
        ],
        'quotation_no_response' => [
            'label' => 'Quotation belum dijawab',
            'default_threshold_days' => 7,
            'priority' => 'medium',
        ],
        'invoice_overdue' => [
            'label' => 'Invois hampir/melepasi tarikh matang',
            'default_threshold_days' => 0,
            'priority' => 'high',
        ],
        'stock_insufficient' => [
            'label' => 'Stok dijangka tidak mencukupi',
            'priority' => 'medium',
        ],
        'campaign_over_budget' => [
            'label' => 'Campaign melebihi bajet',
            'priority' => 'high',
        ],
        'ai_usage_limit' => [
            'label' => 'Penggunaan AI hampir had',
            'default_threshold_percent' => 80,
            'priority' => 'medium',
        ],
        'integration_failure' => [
            'label' => 'Integrasi payment bermasalah',
            'priority' => 'critical',
        ],
        'sales_target_behind' => [
            'label' => 'Sasaran jualan ketinggalan',
            'priority' => 'medium',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Independence (BAB 17A)
    |--------------------------------------------------------------------------
    |
    | Memory persistent, model swappable.
    |
    */

    'model_independence' => [
        'memory_is_model_agnostic' => true,
        'context_builder_loads_same_context_on_model_switch' => true,
        'preferred_model_per_tenant' => true,
        'preferred_model_per_user' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Builder Settings (BAB 17A.5)
    |--------------------------------------------------------------------------
    */

    'context_builder' => [
        'include_business_profile' => true,
        'include_conversation_history' => true,
        'conversation_history_limit' => 20,
        'include_memory_items' => true,
        'memory_items_limit' => 30,
        'include_active_insights' => true,
        'insights_limit' => 10,
        'include_vector_search' => false, // Aktifkan bila Qdrant disediakan
        'vector_search_top_k' => 5,
        'max_context_tokens' => 8000,
        'cache_ttl_seconds' => 300,
    ],

];
