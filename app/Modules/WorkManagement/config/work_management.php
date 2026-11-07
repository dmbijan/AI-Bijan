<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WorkManagement — pengurusan kerja lanjutan ala OpenProject
    |--------------------------------------------------------------------------
    |
    | inspired oleh https://github.com/opf/openproject
    |
    | Melengkapkan modul `Projects` dengan:
    |   - garis masa Gantt + kebergantungan aktiviti
    |   - laluan kritikal
    |   - garis dasar (baseline) & analisis variance
    |   - bajet per baris
    |   - portfolio projek
    |
    */

    'enabled' => (bool) env('WORK_MANAGEMENT_ENABLED', true),

    /*
    | Had
    */
    'limits' => [
        'max_activities_per_project' => (int) env('WORK_MGMT_MAX_ACTIVITIES', 500),
        'max_baselines_per_project' => (int) env('WORK_MGMT_MAX_BASELINES', 20),
        'max_budget_lines' => (int) env('WORK_MGMT_MAX_BUDGET_LINES', 200),
        'max_portfolios' => (int) env('WORK_MGMT_MAX_PORTFOLIOS', 20),
    ],

    /*
    | Garis masa
    */
    'timeline' => [
        'default_type' => env('WORK_MGMT_TIMELINE_TYPE', 'gantt'),
        'auto_schedule' => (bool) env('WORK_MGMT_AUTO_SCHEDULE', true),
        // Jenis kebergantungan: FS (finish-start), SS, FF, SF
        'dependency_types' => ['FS', 'SS', 'FF', 'SF'],
        'default_dependency' => 'FS',
        'max_lag_days' => (int) env('WORK_MGMT_MAX_LAG', 90),
    ],

    /*
    | Laluan kritikal
    */
    'critical_path' => [
        'enabled' => (bool) env('WORK_MGMT_CRITICAL_PATH', true),
        // Toleransi hari sebelum aktiviti dianggap kritikal
        'tolerance_days' => (int) env('WORK_MGMT_CP_TOLERANCE', 0),
    ],

    /*
    | Garis dasar
    */
    'baseline' => [
        'auto_capture' => (bool) env('WORK_MGMT_AUTO_BASELINE', false),
        'capture_interval_days' => (int) env('WORK_MGMT_BASELINE_INTERVAL', 7),
        'retain' => (int) env('WORK_MGMT_RETAIN_BASELINES', 20),
    ],

    /*
    | Bajet
    */
    'budget' => [
        'warn_threshold_percent' => (int) env('WORK_MGMT_BUDGET_WARN', 80),
        'over_threshold_percent' => (int) env('WORK_MGMT_BUDGET_OVER', 100),
        'currency' => env('WORK_MGMT_CURRENCY', 'MYR'),
    ],

];
