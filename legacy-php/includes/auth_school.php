<?php
// Aggressive cache prevention
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Precise session authentication
if (!isset($_SESSION['user_id']) || $_SESSION['subscription_status'] != 'active') {
    header("Location: login.php");
    exit();
}

/**
 * Check if a feature is allowed for the current subscription plan
 */
function hasFeature($feature)
{
    // Auto-allow all features for Demo or Trial accounts
    if (isset($_SESSION['is_demo']) && $_SESSION['is_demo'] === true) {
        return true;
    }

    if (!isset($_SESSION['subscription_plan']))
        return false;

    $plan = $_SESSION['subscription_plan'];

    // Define feature access by plan
    $features = [
        'Trial (1 Day)' => ['students', 'teachers', 'exams', 'billing', 'attendance', 'id_cards'],
        '1_year' => ['students', 'teachers', 'exams'],
        '2_years' => ['students', 'teachers', 'exams', 'billing', 'id_cards'],
        '5_years' => ['students', 'teachers', 'exams', 'billing', 'attendance', 'id_cards', 'teacher_salary']
    ];

    if (!isset($features[$plan]))
        return false;

    return in_array($feature, $features[$plan]);
}

/**
 * Enforce feature gate on a page
 */
function restrictFeature($feature)
{
    if (!hasFeature($feature)) {
        header("Location: dashboard.php?error=unauthorized_feature");
        exit();
    }
}
?>

<!-- Client-side Tab Isolation Security -->
<script>
    (function () {
        // Check if this tab has been authorized to access the portal
        if (!sessionStorage.getItem('smart_portal_tab_verified')) {
            // New tab or direct URL entry detected - force re-verification
            window.location.href = 'login.php?tab_verify=1';
        }
    })();
</script>
<?php
// Continue to page content...
?>