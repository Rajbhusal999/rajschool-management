<?php
require 'includes/db_connect.php';

$email = 'demo@smartvidhyalaya.com';
$stmt = $conn->prepare("SELECT * FROM schools WHERE email = ?");
$stmt->execute([$email]);
$school = $stmt->fetch(PDO::FETCH_ASSOC);

echo "School Found: " . ($school ? "Yes" : "No") . "\n";
if ($school) {
    echo "ID: " . $school['id'] . "\n";
    echo "Plan: " . $school['subscription_plan'] . "\n";
    echo "Expiry: " . $school['subscription_expiry'] . "\n";

    // Try Updating
    $new_expiry = date('Y-m-d', strtotime('+1 day'));
    echo "Updating to: Trial, $new_expiry\n";

    $update = $conn->prepare("UPDATE schools SET subscription_plan = 'Trial', subscription_expiry = ? WHERE id = ?");
    if ($update->execute([$new_expiry, $school['id']])) {
        echo "Update Success!\n";
    } else {
        echo "Update Failed!\n";
        print_r($update->errorInfo());
    }

    // Verify
    $stmt->execute([$email]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "New Plan: " . $school['subscription_plan'] . "\n";
    echo "New Expiry: " . $school['subscription_expiry'] . "\n";
}
?>