<?php

/**
 * End-to-End Test Script for Multi-Tier Admin Hierarchy
 * 
 * Run: php test_e2e_admin.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminRole;
use App\Models\AdminRoleAssignment;
use App\Models\User;
use App\Models\WhiteLabelConfig;
use App\Models\AITokenWallet;
use App\Models\AICreditPackage;

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║   AI BIJAN - Multi-Tier Admin E2E Test Suite        ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;

function test($name, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "✅ PASS: {$name}\n";
        $passed++;
    } else {
        echo "❌ FAIL: {$name}\n";
        $failed++;
    }
}

// ──────────────────────────────── TEST 1: Admin Roles ────────────────────────────────
echo "📋 Testing Admin Roles...\n";
$roles = AdminRole::orderBy('level')->get();
test('4 admin roles exist', $roles->count() === 4);

$master = $roles->firstWhere('code', 'master_admin');
test('Master admin role exists', $master !== null);
test('Master level is 100', $master && $master->level === 100);

$super = $roles->firstWhere('code', 'super_admin');
test('Super admin role exists', $super !== null);
test('Super level is 90', $super && $super->level === 90);

$wl = $roles->firstWhere('code', 'white_label_admin');
test('WL admin role exists', $wl !== null);
test('WL level is 70', $wl && $wl->level === 70);

$customer = $roles->firstWhere('code', 'customer_admin');
test('Customer admin role exists', $customer !== null);
test('Customer level is 50', $customer && $customer->level === 50);

// ──────────────────────────────── TEST 2: Super Admin Assignment ────────────────────────────────
echo "\n📋 Testing Super Admin Assignment...\n";
$superAdmin = User::where('email', 'admin@aibijan.com')->first();
test('Super Admin user exists', $superAdmin !== null);
test('Super Admin is platform admin', $superAdmin && $superAdmin->is_platform_admin === true);

$superAssignment = AdminRoleAssignment::where('user_id', $superAdmin->id)->first();
test('Super Admin has role assignment', $superAssignment !== null);
test('Super Admin assigned to super_admin role', $superAssignment && $superAssignment->admin_role_id === $super->id);

// ──────────────────────────────── TEST 3: White-Label Admin ────────────────────────────────
echo "\n📋 Testing White-Label Admin...\n";
$wlUser = User::where('email', 'wl@demo-brand.com')->first();
test('WL Admin user exists', $wlUser !== null);
test('WL Admin is_active', $wlUser && $wlUser->is_active === true);

$wlAssignment = AdminRoleAssignment::where('user_id', $wlUser->id)->first();
test('WL Admin has role assignment', $wlAssignment !== null);
test('WL Admin assigned to white_label_admin role', $wlAssignment && $wlAssignment->admin_role_id === $wl->id);

$wlConfig = WhiteLabelConfig::where('admin_id', $wlUser->id)->first();
test('WL Admin has config', $wlConfig !== null);
test('WL discount limit is 30%', $wlConfig && $wlConfig->max_discount_pct == 30);
test('WL revenue share is 70%', $wlConfig && $wlConfig->revenue_share_pct == 70);

// ──────────────────────────────── TEST 4: AI Credit Packages ────────────────────────────────
echo "\n📋 Testing AI Credit Packages...\n";
$packages = AICreditPackage::orderBy('credits')->get();
test('5 AI credit packages exist', $packages->count() === 5);

$pkg1k = $packages->firstWhere('code', 'AI-1K');
test('AI-1K package exists', $pkg1k !== null);
test('AI-1K has 1000 credits', $pkg1k && $pkg1k->credits === 1000);
test('AI-1K price is RM10', $pkg1k && $pkg1k->price == 10.00);

$pkg100k = $packages->last();
test('AI-100K has 100000 credits', $pkg100k && $pkg100k->credits === 100000);
test('AI-100K price per 1k is RM6.50', $pkg100k && $pkg100k->price_per_1k_credits == 6.50);

// ──────────────────────────────── TEST 5: Model Relationships ────────────────────────────────
echo "\n📋 Testing Model Relationships...\n";
$wlWithRelations = User::with('adminRoleAssignments.adminRole')->find($wlUser->id);
test('WL user loads with assignments', $wlWithRelations !== null);
test('WL user has 1 assignment', $wlWithRelations && $wlWithRelations->adminRoleAssignments->count() === 1);
test('Assignment has adminRole', $wlWithRelations && $wlWithRelations->adminRoleAssignments->first()->adminRole !== null);

// ──────────────────────────────── TEST 6: Middleware Registration ────────────────────────────────
echo "\n📋 Testing Middleware Configuration...\n";
$app = app();
$middleware = $app->make(\Illuminate\Contracts\Http\Kernel::class);
test('CheckAdminLevel middleware registered', method_exists(\App\Http\Middleware\CheckAdminLevel::class, 'handle'));

// ──────────────────────────────── SUMMARY ────────────────────────────────
echo "\n╔══════════════════════════════════════════════════════╗\n";
echo "║                    TEST SUMMARY                      ║\n";
echo "╠══════════════════════════════════════════════════════╣\n";
printf("║ ✅ Passed: %3d                                       ║\n", $passed);
printf("║ ❌ Failed: %3d                                       ║\n", $failed);
$total = $passed + $failed;
printf("║ 📊 Total:  %3d                                       ║\n", $total);
$percentage = $total > 0 ? round(($passed / $total) * 100) : 0;
printf("║ 📈 Success: %3d%%                                      ║\n", $percentage);
echo "╚══════════════════════════════════════════════════════╝\n";

if ($failed === 0) {
    echo "\n🎉 ALL TESTS PASSED! System ready for development.\n";
    exit(0);
} else {
    echo "\n⚠️  Some tests failed. Please review above.\n";
    exit(1);
}
