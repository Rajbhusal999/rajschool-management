<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['subscription_status'] = 'active';
$_SESSION['school_name'] = 'Test';
$_SESSION['school_address'] = 'Test';
$_SESSION['estd_date'] = 'Test';
$_SESSION['school_logo'] = 'Test';
$_GET['class'] = '4';
$_GET['year'] = '2081';
$_GET['exam'] = 'first_terminal';
ob_start();
require 'mark_ledger.php';
$out = ob_get_clean();
echo "Render complete. Size: " . strlen($out) . " bytes.\n";
if (preg_match_all('/(Warning|Fatal error|Parse error|Notice):(.*)in (.*) on line (\d+)/i', $out, $matches)) {
    echo "ERRORS FOUND:\n";
    print_r($matches[0]);
}
