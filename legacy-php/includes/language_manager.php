<?php
// language_manager.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Check for language change request
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ne'])) {
    $_SESSION['lang'] = $_GET['lang'];
    // Redirect back to same page without the lang parameter
    $url = strtok($_SERVER["REQUEST_URI"], '?');
    echo "<script>window.location.href='$url';</script>";
    exit();
}

/**
 * Translation Data
 */
function getTranslations()
{
    return [
        'en' => [
            'dashboard' => 'Dashboard',
            'students' => 'Students',
            'teachers' => 'Teachers',
            'exams' => 'Exams',
            'billing' => 'Billing',
            'attendance' => 'Attendance',
            'id_cards' => 'ID Cards',
            'logout' => 'Log Out',
            'settings' => 'Settings',
            'welcome' => 'Welcome back',
            'accounting_ext' => 'Income Expenditure Management',
            'gov_school_note' => 'If your school is a government school, you can also use income expenditure management.',
            'open_system' => 'Open System',
            // Add more as needed
        ],
        'ne' => [
            'dashboard' => 'ड्यासबोर्ड',
            'students' => 'विद्यार्थी',
            'teachers' => 'शिक्षक',
            'exams' => 'परीक्षा',
            'billing' => 'बिलिङ',
            'attendance' => 'हाजिरी',
            'id_cards' => 'परिचय पत्र',
            'logout' => 'बाहिर निस्कनुहोस्',
            'settings' => 'सेटिङहरू',
            'welcome' => 'स्वागत छ',
            'accounting_ext' => 'आय व्यय व्यवस्थापन',
            'gov_school_note' => 'यदि तपाइँको विद्यालय सरकारी विद्यालय हो भने तपाइँ आय व्यय व्यवस्थापन पनि प्रयोग गर्न सक्नुहुन्छ।',
            'open_system' => 'प्रणाली खोल्नुहोस्',
            // Add more as needed
        ]
    ];
}

/**
 * Translate helper function
 */
function t($key)
{
    $lang = $_SESSION['lang'];
    $translations = getTranslations();

    if (isset($translations[$lang][$key])) {
        return $translations[$lang][$key];
    }

    return $key; // Fallback to key if not found
}
?>