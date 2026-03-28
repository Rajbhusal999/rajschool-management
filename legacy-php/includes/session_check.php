<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || $_SESSION['subscription_status'] != 'active') {
    echo json_encode(['authenticated' => false]);
} else {
    echo json_encode(['authenticated' => true]);
}
exit();
?>