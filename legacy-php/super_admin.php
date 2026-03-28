<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}
require 'includes/db_connect.php';

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    // SECURITY: Validate and sanitize ID parameter
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    // Check if ID is valid (must be a positive integer)
    if ($id === false || $id <= 0) {
        header("Location: super_admin.php?msg=⚠️ Invalid request");
        exit();
    }

    if ($_GET['action'] == 'generate_code') {
        $code = rand(100000, 999999);
        $stmt = $conn->prepare("UPDATE schools SET payment_verification_code = ? WHERE id = ?");
        $stmt->execute([$code, $id]);
        header("Location: super_admin.php?msg=Code generated: $code");
        exit();
    }
    if ($_GET['action'] == 'send_notice') {
        // Get school details
        $stmt = $conn->prepare("SELECT school_name, email, subscription_expiry FROM schools WHERE id = ?");
        $stmt->execute([$id]);
        $school = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($school) {
            $expiry_date = date('F j, Y', strtotime($school['subscription_expiry']));
            $days_left = ceil((strtotime($school['subscription_expiry']) - time()) / (60 * 60 * 24));

            // Try to send real email
            require_once 'includes/email_helper.php';

            // SECURITY: Escape all user data to prevent XSS in email
            $safe_school_name = htmlspecialchars($school['school_name'], ENT_QUOTES, 'UTF-8');
            $safe_days_left = (int) $days_left;
            $safe_expiry_date = htmlspecialchars($expiry_date, ENT_QUOTES, 'UTF-8');

            // Prepare notice email content
            $subject = "⚠️ Subscription Expiry Notice - Action Required";
            $message = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                        <h1 style='color: white; margin: 0;'>Subscription Expiry Notice</h1>
                    </div>
                    <div style='background: #f9fafb; padding: 30px;'>
                        <p style='font-size: 16px; color: #1f2937;'>Dear <strong>{$safe_school_name}</strong>,</p>
                        
                        <div style='background: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0;'>
                            <p style='color: #991b1b; margin: 0; font-weight: 600;'>⚠️ Your subscription is expiring soon!</p>
                        </div>
                        
                        <p style='font-size: 16px; color: #374151;'>
                            Your subscription will expire in <strong style='color: #dc2626; font-size: 18px;'>{$safe_days_left} day(s)</strong> on <strong>{$safe_expiry_date}</strong>.
                        </p>
                        
                        <p style='font-size: 16px; color: #374151;'>
                            To continue enjoying uninterrupted access to all features of Smart विद्यालय, please renew your subscription before it expires.
                        </p>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='http://localhost/student%20management/subscription_plans.php' 
                               style='background: #0ea5e9; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 600;'>
                                Renew Now
                            </a>
                        </div>
                        
                        <p style='font-size: 14px; color: #6b7280;'>
                            If you have any questions, please don't hesitate to contact our support team.
                        </p>
                        
                        <p style='font-size: 14px; color: #6b7280;'>
                            Thank you for choosing Smart विद्यालय!
                        </p>
                    </div>
                    <div style='background: #1f2937; padding: 20px; text-align: center;'>
                        <p style='color: #9ca3af; margin: 0; font-size: 12px;'>
                            © 2026 Smart विद्यालय. All rights reserved.
                        </p>
                    </div>
                </div>
            ";

            $email_result = sendCustomEmail($school['email'], $safe_school_name, $subject, $message);

            if ($email_result['success']) {
                header("Location: super_admin.php?msg=✓ Expiry notice sent to {$school['email']}");
            } else {
                header("Location: super_admin.php?msg=⚠️ Notice generated but email not sent (check configuration)");
            }
            exit();
        }
    }
    if ($_GET['action'] == 'send_code') {
        // Generate new verification code
        $code = rand(100000, 999999);

        // Get school details
        $stmt = $conn->prepare("SELECT school_name, email FROM schools WHERE id = ?");
        $stmt->execute([$id]);
        $school = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($school) {
            // Update the verification code in database
            $stmt = $conn->prepare("UPDATE schools SET payment_verification_code = ? WHERE id = ?");
            $stmt->execute([$code, $id]);

            // Try to send real email
            require_once 'includes/email_helper.php';
            $email_result = sendVerificationCode($school['email'], $school['school_name'], $code);

            if ($email_result['success']) {
                // Email sent successfully
                header("Location: super_admin.php?msg=✓ Verification code sent to {$school['email']}");
            } else {
                // Email failed, show simulation instead
                $email_content = "
                    <div style='background: #fee2e2; color: #991b1b; padding: 20px; border: 2px solid #dc2626; margin: 20px 0; border-radius: 8px;'>
                        <h3 style='color: #dc2626;'>⚠️ Email Configuration Required</h3>
                        <p><strong>Error:</strong> {$email_result['message']}</p>
                        <p>Please configure your email settings in <code>includes/email_config.php</code></p>
                    </div>
                    <div style='background: white; color: black; padding: 20px; border: 2px solid #0ea5e9; margin: 20px 0; border-radius: 8px;'>
                        <h3 style='color: #0ea5e9;'>[EMAIL PREVIEW - Not Sent]</h3>
                        <p><strong>To:</strong> {$school['email']}</p>
                        <p><strong>Subject:</strong> Your Payment Verification Code</p>
                        <hr>
                        <p>Dear {$school['school_name']},</p>
                        <p>Your payment verification code is: <strong style='font-size: 24px; color: #0ea5e9; letter-spacing: 3px;'>{$code}</strong></p>
                        <p>Please use this code to verify your payment and activate your subscription.</p>
                        <p>Thank you for choosing Smart विद्यालय!</p>
                    </div>
                ";
                $_SESSION['email_simulation'] = $email_content;
                header("Location: super_admin.php?msg=Code generated but email not sent (check configuration)");
            }
            exit();
        }
    }
    if ($_GET['action'] == 'delete') {
        $stmt = $conn->prepare("DELETE FROM schools WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: super_admin.php?msg=School deleted");
        exit();
    }
}

// Handle Backup Action
if (isset($_GET['action']) && $_GET['action'] == 'backup') {
    // Fetch all schools data
    $stmt = $conn->query("SELECT * FROM schools ORDER BY id ASC");
    $schools_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    $filename = "schools_backup_" . date('Y-m-d_H-i-s') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add BOM for UTF-8 (helps Excel recognize UTF-8)
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Add CSV headers
    if (count($schools_data) > 0) {
        fputcsv($output, array_keys($schools_data[0]));
    }

    // Add data rows
    foreach ($schools_data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}

$schools = $conn->query("SELECT * FROM schools ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Panel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #0f172a;
            color: #e2e8f0;
            background-image: url('assets/images/admin_bg.png');
            background-attachment: fixed;
            background-size: cover;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid #1e293b;
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #1e293b;
        }

        .admin-table th {
            background: rgba(30, 41, 59, 0.8);
            color: #38bdf8;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .btn-action {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-right: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-gen {
            background: #0ea5e9;
            color: white;
        }

        .btn-del {
            background: #ef4444;
            color: white;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        .badge-active {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid #059669;
        }

        .badge-inactive {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid #dc2626;
        }

        .btn-action:hover {
            opacity: 0.8;
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        .btn-notice {
            background: #f59e0b;
            color: white;
        }

        .btn-notice:hover {
            background: #d97706;
        }
    </style>
</head>

<body>

    <div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="color: #38bdf8;">Command Center</h1>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <a href="?action=backup"
                    style="background: #10b981; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; transition: background 0.3s;"
                    onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    <i class="fas fa-download"></i>
                    Backup Users Data
                </a>
                <a href="admin.php" style="color: #94a3b8;">Logout</a>
            </div>
        </div>


        <?php if (isset($_GET['msg'])): ?>
            <div id="successMessage"
                style="background: rgba(16, 185, 129, 0.2); border: 1px solid #34d399; color: #34d399; padding: 1rem; margin-bottom: 2rem; border-radius: 8px; transition: opacity 0.5s ease-out, transform 0.5s ease-out;">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
            <script>
                // Clean URL to prevent message from showing on refresh
                if (window.history.replaceState) {
                    var cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
                }

                // Auto-dismiss message after 5 seconds
                setTimeout(function () {
                    var msgElement = document.getElementById('successMessage');
                    if (msgElement) {
                        // Fade out animation
                        msgElement.style.opacity = '0';
                        msgElement.style.transform = 'translateY(-20px)';

                        // Remove from DOM after animation completes
                        setTimeout(function () {
                            msgElement.remove();
                        }, 500); // Wait for fade-out animation to complete
                    }
                }, 5000); // 5 seconds
            </script>
        <?php endif; ?>

        <?php if (isset($_SESSION['email_simulation'])): ?>
            <?php echo $_SESSION['email_simulation']; ?>
            <?php unset($_SESSION['email_simulation']); // Clear after displaying ?>
        <?php endif; ?>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>School Name</th>
                        <th>EMIS Code</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Joining Date</th>
                        <th>Expiry Date</th>
                        <th>Days Left</th>
                        <th>Verif. Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $schools->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td>#
                                <?php echo $row['id']; ?>
                            </td>
                            <td style="font-weight: 600; color: white;">
                                <?php echo htmlspecialchars($row['school_name']); ?>
                            </td>
                            <td style="font-family: monospace; color: #cbd5e1;">
                                <?php echo htmlspecialchars($row['emis_code']); ?>
                            </td>
                            <td style="color: #94a3b8;">
                                <a href="?action=send_code&id=<?php echo $row['id']; ?>"
                                    style="color: #38bdf8; text-decoration: none; cursor: pointer; border-bottom: 1px dashed #38bdf8;"
                                    title="Click to send verification code to this email">
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['phone']); ?>
                            </td>
                            <td>
                                <span
                                    class="badge <?php echo $row['subscription_status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo strtoupper($row['subscription_status']); ?>
                                </span>
                            </td>
                            <td style="color: #cbd5e1; font-size: 0.85rem;">
                                <?php echo $row['created_at'] ? date('Y-m-d', strtotime($row['created_at'])) : '-'; ?>
                            </td>
                            <td
                                style="color: <?php echo (strtotime($row['subscription_expiry']) < time()) ? '#f87171' : '#34d399'; ?>; font-size: 0.85rem; font-weight: 600;">
                                <?php echo $row['subscription_expiry'] ? date('Y-m-d', strtotime($row['subscription_expiry'])) : '-'; ?>
                            </td>
                            <td style="font-size: 0.9rem; font-weight: 700;">
                                <?php
                                if ($row['subscription_expiry']) {
                                    $days_left = ceil((strtotime($row['subscription_expiry']) - time()) / (60 * 60 * 24));
                                    $color = '#34d399'; // Green by default
                                    $bg_color = 'rgba(16, 185, 129, 0.1)';

                                    if ($days_left <= 0) {
                                        $color = '#f87171'; // Red for expired
                                        $bg_color = 'rgba(239, 68, 68, 0.2)';
                                        $display_text = 'EXPIRED';
                                    } elseif ($days_left <= 2) {
                                        $color = '#ef4444'; // Bright red for critical
                                        $bg_color = 'rgba(239, 68, 68, 0.2)';
                                        $display_text = $days_left . ' day' . ($days_left > 1 ? 's' : '');
                                    } elseif ($days_left <= 7) {
                                        $color = '#facc15'; // Yellow for warning
                                        $bg_color = 'rgba(250, 204, 21, 0.2)';
                                        $display_text = $days_left . ' days';
                                    } else {
                                        $display_text = $days_left . ' days';
                                    }

                                    echo "<span style='color: $color; background: $bg_color; padding: 4px 8px; border-radius: 4px; display: inline-block;'>$display_text</span>";
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td style="font-family: monospace; font-size: 1.1rem; letter-spacing: 2px; color: #facc15;">
                                <?php echo $row['payment_verification_code'] ? $row['payment_verification_code'] : '-'; ?>
                            </td>
                            <td>
                                <a href="?action=generate_code&id=<?php echo $row['id']; ?>"
                                    class="btn-action btn-gen">Generate Code</a>
                                <?php
                                // Show "Send Notice" button if subscription is ending in 2 days or less
                                if ($row['subscription_expiry']) {
                                    $days_left = ceil((strtotime($row['subscription_expiry']) - time()) / (60 * 60 * 24));
                                    if ($days_left > 0 && $days_left <= 2) {
                                        echo '<a href="?action=send_notice&id=' . $row['id'] . '" 
                                              class="btn-action" 
                                              style="background: #f59e0b; color: white;"
                                              title="Send subscription expiry notice">Send Notice</a>';
                                    }
                                }
                                ?>
                                <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-del"
                                    onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>