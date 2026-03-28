<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Billing History - Smart विद्यालय</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .btn-back {
            background: #64748b;
        }

        .btn-new {
            background: #ec4899;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: #f8fafc;
        }

        .topics-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            color: #64748b;
        }

        .amount {
            font-weight: bold;
            color: #0f172a;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: #94a3b8;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <div class="title">Donor Billing History</div>
            <div style="display: flex; gap: 10px;">
                <a href="donor_billing.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Back to Receipt</a>
                <a href="donor_billing.php" class="btn btn-new"><i class="fas fa-plus"></i> New Receipt</a>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Date</th>
                    <th>Run Date (Issued)</th>
                    <th>Donor Name</th>
                    <th>Details (Topics)</th>
                    <th style="text-align: right;">Amount</th>
                    <th style="width: 100px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch receipts
                try {
                    $sql = "SELECT * FROM donor_receipts WHERE school_id = ? ORDER BY receipt_no DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$school_id]);
                    $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($receipts) > 0) {
                        foreach ($receipts as $r) {
                            $name = isset($r['donor_name']) ? htmlspecialchars($r['donor_name']) : 'N/A';
                            $topics = isset($r['topics']) ? htmlspecialchars($r['topics']) : '-';
                            $amount = isset($r['total_amount']) ? number_format($r['total_amount'], 2) : '0.00';
                            $created = date('Y-m-d H:i', strtotime($r['created_at']));

                            echo "<tr>";
                            echo "<td style='font-weight: bold; color: #0369a1;'>#{$r['receipt_no']}</td>";
                            echo "<td>{$r['receipt_date']}</td>";
                            echo "<td style='font-size: 12px; color: #999;'>{$created}</td>";
                            echo "<td>{$name}</td>";
                            echo "<td class='topics-cell' title='{$topics}'>{$topics}</td>";
                            echo "<td style='text-align: right;' class='amount'>Rs. {$amount}</td>";
                            echo "<td>
                                <a href='donor_billing.php?print_receipt={$r['receipt_no']}' target='_blank' style='color: #2563eb; text-decoration: none; font-weight: bold;'>
                                    <i class='fas fa-print'></i> Print
                                </a>
                              </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='empty-state'>No receipts generated yet.</td></tr>";
                    }
                } catch (Exception $e) {
                    echo "<tr><td colspan='7' style='color: red;'>Error loading data: " . $e->getMessage() . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>

</html>