<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Studio (BAB 84 / Idea #1)
    |--------------------------------------------------------------------------
    |
    | Tetapan Model Context Protocol server AI BIJAN.
    |
    */

    'server' => [
        'name' => env('MCP_SERVER_NAME', 'AI BIJAN'),
        'version' => env('MCP_SERVER_VERSION', '1.0.0'),
        'protocol_version' => env('MCP_PROTOCOL_VERSION', '2025-06-18'),
        // Benarkan klien luaran tanpa token (untuk IDE tempatan sahaja)
        'allow_anonymous' => (bool) env('MCP_ALLOW_ANONYMOUS', false),
    ],

    'safety' => [
        // Had baris maksimum untuk senarai/read
        'max_rows' => (int) env('MCP_MAX_ROWS', 100),
        'default_rows' => (int) env('MCP_DEFAULT_ROWS', 25),
        // Alat destructive WAJIB kelulusan manusia
        'require_approval_for_destructive' => (bool) env('MCP_REQUIRE_APPROVAL_DESTRUCTIVE', true),
        // Had tempoh satu panggilan (ms)
        'timeout_ms' => (int) env('MCP_TIMEOUT_MS', 30000),
    ],

    'audit' => [
        'enabled' => (bool) env('MCP_AUDIT_ENABLED', true),
        'retention_days' => (int) env('MCP_AUDIT_RETENTION_DAYS', 180),
    ],

    'clients' => [
        // Known MCP clients untuk onboarding
        'known' => [
            ['name' => 'Claude Desktop', 'config' => 'claude_desktop_config.json'],
            ['name' => 'Cursor', 'config' => '~/.cursor/mcp.json'],
            ['name' => 'Windsurf', 'config' => '~/.codeium/windsurf/mcp_config.json'],
            ['name' => 'n8n', 'config' => 'MCP Client Tool node'],
            ['name' => 'Zapier', 'config' => 'MCP Zap'],
        ],
    ],
];
