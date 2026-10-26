<?php

// AI Agents — Configuration untuk 11 ejen AI
return [
    // 11 Ejen AI dengan tools spesifik
    'agents' => [
        'ceo' => [
            'id' => 'ai_ceo',
            'name' => 'AI CEO / Chief of Staff',
            'description' => 'Menyelaraskan matlamat, delegasi dan ringkasan eksekutif',
            'role' => 'executive',
            'system_prompt' => 'You are the AI CEO / Chief of Staff for this business. Your role is to align goals, delegate tasks to other agents, and provide executive summaries.',
            'tools' => ['create_task', 'assign_agent', 'summarize_workspace', 'get_kpi_dashboard', 'escalate_issue'],
            'budget_limits' => [
                'max_cost_per_run' => 0.50,
                'max_tokens_per_run' => 8000,
                'max_actions_per_run' => 10,
            ],
            'approval_required_for' => ['financial_commitment', 'contract_signing', 'hiring_decision'],
            'data_access' => ['all_modules', 'reports', 'finance', 'hrms', 'crm'],
            'memory_enabled' => true,
            'proactive_alerts' => true,
        ],
        
        'exec_assistant' => [
            'id' => 'ai_exec_assistant',
            'name' => 'AI Executive Assistant',
            'description' => 'Kalendar, e-mel, nota, susulan dan jadual',
            'role' => 'administrative',
            'system_prompt' => 'You are the AI Executive Assistant. Help manage calendars, emails, notes, follow-ups and schedules.',
            'tools' => ['calendar.read', 'calendar.write', 'email.draft', 'email.send', 'task.create', 'task.update', 'notes.create', 'reminders.set'],
            'budget_limits' => [
                'max_cost_per_run' => 0.20,
                'max_tokens_per_run' => 4000,
                'max_actions_per_run' => 20,
            ],
            'approval_required_for' => ['email.send_external'],
            'data_access' => ['calendar', 'email', 'tasks', 'notes'],
            'memory_enabled' => true,
            'proactive_alerts' => true,
        ],
        
        'cmo' => [
            'id' => 'ai_cmo',
            'name' => 'AI CMO',
            'description' => 'Strategi pemasaran, kandungan, kempen dan analisis saluran',
            'role' => 'marketing',
            'system_prompt' => 'You are the AI Chief Marketing Officer. Develop marketing strategies, create content, manage campaigns and analyze channel performance.',
            'tools' => ['campaign.draft', 'campaign.publish*', 'ads.read', 'ads.optimize', 'content.generate', 'audience.segment', 'analytics.read', 'seo.audit'],
            'budget_limits' => [
                'max_cost_per_run' => 0.40,
                'max_tokens_per_run' => 6000,
                'max_actions_per_run' => 15,
            ],
            'approval_required_for' => ['campaign.publish', 'ad_spend_increase', 'pricing_change'],
            'data_access' => ['marketing', 'crm', 'analytics', 'site', 'social_media'],
            'memory_enabled' => true,
            'proactive_alerts' => true,
        ],
        
        'cfo' => [
            'id' => 'ai_cfo',
            'name' => 'AI CFO',
            'description' => 'Aliran tunai, bajet, invois, margin dan amaran risiko',
            'role' => 'finance',
            'system_prompt' => 'You are the AI Chief Financial Officer. Monitor cash flow, budgets, invoices, margins and provide risk alerts.',
            'tools' => ['finance.read', 'report.generate', 'invoice.draft', 'cashflow.forecast', 'budget.monitor', 'risk.alert', 'reconciliation.check'],
            'budget_limits' => [
                'max_cost_per_run' => 0.35,
                'max_tokens_per_run' => 5000,
                'max_actions_per_run' => 12,
            ],
            'approval_required_for' => ['payment.approve', 'journal.post', 'credit_extension'],
            'data_access' => ['finance', 'invoicing', 'payroll', 'inventory'],
            'memory_enabled' => true,
            'proactive_alerts' => true,
        ],
        
        'coo' => [
            'id' => 'ai_coo',
            'name' => 'AI COO',
            'description' => 'SOP, operasi, inventori, tugasan dan kapasiti',
            'role' => 'operations',
            'system_prompt' => 'You are the AI Chief Operating Officer. Manage SOPs, operations, inventory, tasks and capacity planning.',
            'tools' => ['inventory.read', 'inventory.write', 'task.assign', 'operations.report', 'sop.create', 'capacity.plan', 'vendor.manage'],
            'budget_limits' => [
                'max_cost_per_run' => 0.30,
                'max_tokens_per_run' => 5000,
                'max_actions_per_run' => 15,
            ],
            'approval_required_for' => ['purchase_order', 'stock_adjustment', 'vendor_contract'],
            'data_access' => ['inventory', 'manufacturing', 'logistics', 'projects', 'procurement'],
            'memory_enabled' => true,
            'proactive_alerts' => true,
        ],
        
        'ciso' => [
            'id' => 'ai_cso',
            'name' => 'AI CSO',
            'description' => 'Leads, pipeline jualan, follow-up dan affiliate',
            'role' => 'sales',
            'system_prompt' => 'You are the AI Chief Sales Officer. Manage leads, sales pipeline, follow-ups and affiliate programs.',
            'tools' => ['crm.read', 'crm.write', 'lead.update', 'lead.score', 'sales.report', 'pipeline.analyze', 'affiliate.manage', 'quote.draft'],
            'budget_limits' => [
                'max_cost_per_run' => 0.30,
                'max_tokens_per_run' => 5000,
                'max_actions_per_run' => 15,
            ],
            'approval_required_for' => ['discount_approval', 'credit_term', 'contract_signing'],
            'data_access' => ['crm', 'marketplace', 'affiliate', 'loyalty'],
            'memory_enabled' => true,
            'proactive_alerts' => true,
        ],
        
        'chro' => [
            'id' => 'ai_chro',
            'name' => 'AI CHRO',
            'description' => 'Kakitangan, jadual, cuti, onboarding dan HR',
            'role' => 'hr',
            'system_prompt' => 'You are the AI Chief Human Resources Officer. Manage employees, schedules, leave, onboarding and HR policies.',
            'tools' => ['hr.read', 'hr.write', 'shift.draft', 'leave.review', 'onboarding.create', 'policy.qa', 'performance.track'],
            'budget_limits' => [
                'max_cost_per_run' => 0.25,
                'max_tokens_per_run' => 4000,
                'max_actions_per_run' => 10,
            ],
            'approval_required_for' => ['hire_decision', 'salary_change', 'termination'],
            'data_access' => ['hrms', 'payroll', 'attendance', 'recruitment'],
            'memory_enabled' => true,
            'proactive_alerts' => true,
        ],
        
        'support' => [
            'id' => 'ai_support',
            'name' => 'AI Customer Support',
            'description' => 'Soalan pelanggan, FAQ, pesanan dan eskalasi',
            'role' => 'support',
            'system_prompt' => 'You are the AI Customer Support agent. Answer customer questions, handle FAQs, process orders and escalate when needed.',
            'tools' => ['knowledge.search', 'conversation.reply_draft', 'ticket.classify', 'order.lookup', 'refund.process*', 'escalate.ticket'],
            'budget_limits' => [
                'max_cost_per_run' => 0.15,
                'max_tokens_per_run' => 3000,
                'max_actions_per_run' => 25,
            ],
            'approval_required_for' => ['refund_over_50', 'policy_exception'],
            'data_access' => ['omnichannel', 'helpdesk', 'orders', 'knowledge_base'],
            'memory_enabled' => true,
            'proactive_alerts' => false,
        ],
        
        'ads_specialist' => [
            'id' => 'ai_ads_specialist',
            'name' => 'AI Ads Specialist',
            'description' => 'Google Ads, Meta Ads dan pengoptimuman kempen',
            'role' => 'marketing',
            'system_prompt' => 'You are the AI Ads Specialist. Manage Google Ads, Meta Ads campaigns and optimize for performance.',
            'tools' => ['ads.performance.read', 'campaign.draft', 'campaign.publish*', 'budget.optimize', 'audience.test', 'creative.generate', 'conversion.track'],
            'budget_limits' => [
                'max_cost_per_run' => 0.35,
                'max_tokens_per_run' => 5000,
                'max_actions_per_run' => 12,
            ],
            'approval_required_for' => ['budget_increase', 'campaign_publish', 'bid_change_over_20pct'],
            'data_access' => ['marketing', 'ads_integrations', 'analytics'],
            'memory_enabled' => true,
            'proactive_alerts' => true,
        ],
        
        'researcher' => [
            'id' => 'ai_researcher',
            'name' => 'AI Researcher / Scout',
            'description' => 'Trend, pesaing dan sumber maklumat',
            'role' => 'research',
            'system_prompt' => 'You are the AI Researcher / Scout. Find trends, competitor analysis and gather information from authorized sources.',
            'tools' => ['web.research', 'competitor.compare', 'trend.analyze', 'market.scan', 'source.validate', 'report.generate'],
            'budget_limits' => [
                'max_cost_per_run' => 0.45,
                'max_tokens_per_run' => 10000,
                'max_actions_per_run' => 8,
            ],
            'approval_required_for' => [],
            'data_access' => ['external_sources', 'analytics', 'crm', 'marketing'],
            'memory_enabled' => true,
            'proactive_alerts' => false,
        ],
        
        'analyst' => [
            'id' => 'ai_analyst',
            'name' => 'AI Analyst',
            'description' => 'Analisis KPI, ramalan dan insight',
            'role' => 'analytics',
            'system_prompt' => 'You are the AI Analyst. Perform KPI analysis, generate forecasts and provide data-driven insights.',
            'tools' => ['analytics.query', 'forecast.generate', 'trend.analyze', 'dashboard.create', 'metric.compare', 'insight.generate', 'report.export'],
            'budget_limits' => [
                'max_cost_per_run' => 0.40,
                'max_tokens_per_run' => 8000,
                'max_actions_per_run' => 10,
            ],
            'approval_required_for' => [],
            'data_access' => ['analytics', 'brainhub', 'finance', 'crm', 'inventory', 'all_reports'],
            'memory_enabled' => true,
            'proactive_alerts' => true,
        ],
    ],

    // Model routing per task type
    'model_routing' => [
        'reasoning' => ['asai/gpt-6-astra', 'asai/claude-opus-5'],
        'coding' => ['asai/deepseek-v4-pro'],
        'creative' => ['asai/muse-wonder-1-0'],
        'quick_response' => ['asai/glm-5-3-flash'],
        'long_context' => ['asai/kimi-k3'],
        'multilingual' => ['asai/qwen-3-8-max'],
        'real_time' => ['asai/grok-4-7'],
        'balanced' => ['asai/minimax-m3'],
    ],

    // Approval matrix untuk tindakan sensitif
    'approval_matrix' => [
        'financial_commitment' => ['level' => 'L0', 'required_role' => 'tenant_owner'],
        'contract_signing' => ['level' => 'L0', 'required_role' => 'tenant_owner'],
        'hiring_decision' => ['level' => 'L1', 'required_role' => 'hr_manager'],
        'salary_change' => ['level' => 'L0', 'required_role' => 'tenant_owner'],
        'campaign_publish' => ['level' => 'L1', 'required_role' => 'tenant_admin'],
        'payment_approve' => ['level' => 'L1', 'required_role' => 'finance_manager'],
        'refund_over_50' => ['level' => 'L1', 'required_role' => 'finance_manager'],
        'purchase_order' => ['level' => 'L1', 'required_role' => 'inventory_manager'],
    ],

    // Governance settings
    'governance' => [
        'least_privilege' => true,
        'human_approval_required' => true,
        'tool_allowlists' => true,
        'parameter_validation' => true,
        'audit_all_actions' => true,
        'tenant_data_isolation' => true,
        'uncertainty_disclosure' => true,
        'irreversible_action_confirmation' => true,
    ],
];
