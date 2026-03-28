<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];

// Ensure donor receipt table exists
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS donor_receipts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT NOT NULL,
        receipt_no INT NOT NULL,
        donor_name VARCHAR(255),
        donor_address VARCHAR(255),
        total_amount DECIMAL(10,2),
        topics TEXT,
        receipt_date VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Get Last Receipt Info
    $stmt_last = $conn->prepare("SELECT receipt_no, receipt_date FROM donor_receipts WHERE school_id = ? ORDER BY receipt_no DESC LIMIT 1");
    $stmt_last->execute([$school_id]);
    $last_receipt = $stmt_last->fetch(PDO::FETCH_ASSOC);

    if ($last_receipt) {
        $last_no = $last_receipt['receipt_no'];
        $last_date = $last_receipt['receipt_date'];
        $next_receipt_no = $last_no + 1;
    } else {
        $last_no = 'None';
        $last_date = '-';
        $next_receipt_no = 1001; // Start donors from 1001 to distinguish
    }
} catch (Exception $e) {
    $next_receipt_no = 1001;
}

// Handle Save
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_receipt'])) {
    $r_no = $_POST['receipt_no'];
    $r_date = $_POST['receipt_date'];
    $r_name = $_POST['donor_name'] ?? '';
    $r_address = $_POST['donor_address'] ?? '';

    // Calculate Total and Topic Summary
    $total_amount = 0;
    $topic_summary_parts = [];

    if (isset($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            $desc = $item['desc'] ?? '';
            $amt = floatval($item['amount'] ?? 0);

            if ($amt > 0 || !empty($desc)) {
                if ($amt > 0)
                    $total_amount += $amt;
                if (!empty($desc))
                    $topic_summary_parts[] = "$desc($amt)";
            }
        }
    }
    $topics_str = implode(', ', $topic_summary_parts);

    // Insert into DB
    $stmt_save = $conn->prepare("INSERT INTO donor_receipts (school_id, receipt_no, receipt_date, donor_name, donor_address, total_amount, topics) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_save->execute([$school_id, $r_no, $r_date, $r_name, $r_address, $total_amount, $topics_str]);

    // Redirect to print
    header("Location: donor_billing.php?print_receipt=" . urlencode($r_no));
    exit();
}

// Function to render receipt HTML
function render_receipt($type, $next_receipt_no)
{
    $is_school = ($type === 'school');
    $container_class = $is_school ? 'receipt-school' : 'receipt-student';
    $input_class = $is_school ? 'source-input' : 'mirror-input';
    $readonly = $is_school ? '' : 'readonly';
    $copy_label = $is_school ? 'School Copy' : 'Donor Copy';

    // Get receipt number value
    $r_val = isset($_GET['print_receipt']) ? htmlspecialchars($_GET['print_receipt']) : $next_receipt_no;

    // Date Helper
    require_once 'includes/nepali_date_helper.php';
    $today_np = NepaliDateHelper::convertToNepali(date('Y-m-d'));

    ?>
    <div class="receipt-container <?php echo $container_class; ?>">
        <div class="watermark">
            <?php echo $copy_label; ?>
        </div>
        <div class="receipt-header">
            <div style="position: relative;">
                <?php if (isset($_SESSION['school_logo']) && !empty($_SESSION['school_logo'])): ?>
                    <img src="<?php echo htmlspecialchars($_SESSION['school_logo']); ?>" alt="Logo"
                        style="position: absolute; left: 10px; top: 0; height: 60px; width: auto;">
                <?php endif; ?>

                <h1 class="school-name">
                    <?php echo htmlspecialchars($_SESSION['school_name']); ?>
                </h1>
                <div class="school-address">
                    <?php echo isset($_SESSION['school_address']) ? htmlspecialchars($_SESSION['school_address']) : 'Address Not Found'; ?>
                </div>
                <div style="font-size: 14px; margin: 5px 0; font-weight: bold;">
                    <?php echo isset($_SESSION['estd_date']) ? 'ESTD: ' . htmlspecialchars($_SESSION['estd_date']) : ''; ?>
                </div>
            </div>
            <div><span class="receipt-title">सहयोग रसिद (Donation Receipt)</span></div>
        </div>

        <div class="receipt-meta">
            <div class="meta-left">
                <div style="margin-bottom: 5px;">
                    र. नं. <input type="text" <?php if ($is_school)
                        echo 'name="receipt_no"'; ?> class="
                <?php echo $input_class; ?>" data-field="receipt_no" value="
                <?php echo $r_val; ?>" style="width: 80px;" <?php echo $readonly; ?>>
                </div>
                <!-- Donor Name -->
                <div style="margin-top: 5px;">
                    श्री / सुश्री:
                    <input type="text" <?php if ($is_school)
                        echo 'name="donor_name"'; ?> class="
                <?php echo $input_class; ?>" data-field="donor_name" style="width: 250px;" placeholder="Donor Name"
                        <?php echo $readonly; ?>>
                </div>
                <!-- Address -->
                <div style="margin-top: 5px;">
                    ठेगाना (Address):
                    <input type="text" <?php if ($is_school)
                        echo 'name="donor_address"'; ?> class="
                <?php echo $input_class; ?>" data-field="donor_address" style="width: 250px;" placeholder="Address"
                        <?php echo $readonly; ?>>
                </div>
            </div>
            <div class="meta-right">
                <div style="text-align: right;">
                    मिति: <input type="text" <?php if ($is_school)
                        echo 'name="receipt_date"'; ?> class="
                <?php echo $input_class; ?>" data-field="date" value="
                <?php echo $today_np; ?>" style="width: 100px;" <?php echo $readonly; ?>>
                </div>
            </div>
        </div>

        <table class="fee-table">
            <thead>
                <tr>
                    <th style="width: 40px;">क्र.सं.</th>
                    <th>विवरण (Description)</th>
                    <th style="width: 80px;">रकम (Amount)</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <tr>
                        <td style="text-align: center;">
                            <?php echo $i; ?>
                        </td>
                        <td>
                            <input type="text" <?php if ($is_school)
                                echo 'name="items[' . $i . '][desc]"'; ?> class="desc-input
                    <?php echo $input_class; ?>" data-field="desc_
                    <?php echo $i; ?>" style="width: 100%; text-align: left;" <?php echo $readonly; ?>>
                        </td>
                        <td>
                            <input type="number" step="0.01" <?php if ($is_school)
                                echo 'name="items[' . $i . '][amount]"'; ?>
                                class="fee-amount
                    <?php echo $input_class; ?>" data-field="amount_
                    <?php echo $i; ?>" placeholder="-" <?php echo $readonly; ?>>
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;">जम्मा</td>
                    <td style="text-align: right; padding-right: 5px;">
                        <span id="grandTotal_<?php echo $type; ?>">0.00</span>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 10px;">
            अक्षरेपी: <input type="text" class="form-line <?php echo $input_class; ?>" data-field="words"
                style="width: 70%; text-align: left;" <?php echo $readonly; ?>>
        </div>

        <div class="footer-sign">
            <div class="sign-box">
                <div class="sign-line"></div>
                बुझिलिनेको सही
            </div>
            <div class="sign-box">
                <div class="sign-line"></div>
                दिनेको सही
            </div>
        </div>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Billing - Smart विद्यालय</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Arial Narrow', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
        }

        .sheet {
            display: flex;
            flex-direction: row;
            gap: 2%;
            width: 297mm;
            max-width: 100%;
            margin: 0 auto;
        }

        .receipt-container {
            flex: 1;
            max-width: 48%;
            background: #fdf2f8;
            border: 1px dotted #ccc;
            padding: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            color: #000;
            box-sizing: border-box;
            font-size: 13px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 40px;
            color: rgba(0, 0, 0, 0.05);
            pointer-events: none;
            z-index: 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #000;
            margin: 0;
            text-transform: uppercase;
        }

        .school-address {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .receipt-title {
            display: inline-block;
            border: 1px solid #000;
            padding: 2px 15px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .receipt-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 12px;
            line-height: 1.5;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .meta-left {
            width: 62%;
        }

        .meta-right {
            width: 35%;
            text-align: right;
        }

        input[type="text"],
        input[type="number"] {
            border: none;
            border-bottom: 1px dotted #000;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            font-weight: bold;
            padding: 0 2px;
            outline: none;
            text-align: center;
        }

        .meta-left input[type="text"] {
            text-align: left;
        }

        input[data-field="receipt_no"],
        input[data-field="roll"],
        input[data-field="class"],
        input[data-field="section"],
        input[data-field="month"],
        input[data-field="date"] {
            text-align: center !important;
        }

        .fee-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            position: relative;
            z-index: 1;
        }

        .fee-table th,
        .fee-table td {
            border: 1px solid #000;
            padding: 2px 4px;
        }

        .fee-table th {
            text-align: center;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.5);
        }

        .fee-table input[type="number"] {
            width: 100%;
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            text-align: right;
        }

        .footer-sign {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            position: relative;
            z-index: 1;
        }

        .sign-box {
            text-align: center;
            width: 120px;
        }

        .sign-line {
            border-top: 1px dotted #000;
            margin-bottom: 5px;
        }

        .controls {
            max-width: 800px;
            margin: 0 auto 20px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn {
            background: #ec4899;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .controls {
                display: none;
            }

            .sheet {
                width: 100%;
                max-width: none;
                margin: 0;
                gap: 4%;
            }

            .receipt-container {
                box-shadow: none;
                border: none;
                background-color: #fdf2f8 !important;
                -webkit-print-color-adjust: exact;
            }

            .receipt-container.receipt-school {
                border-right: 1px dashed #ccc;
            }
        }
    </style>
</head>

<body>

    <div class="controls">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="billing.php" style="color: #666; text-decoration: none;"><i class="fas fa-arrow-left"></i>
                    Back</a>
                <h2 style="margin: 0; color: #333;">New Donor Receipt</h2>
            </div>

            <div style="display: flex; gap: 15px; align-items: center;">
                <a href="donor_billing_history.php" class="btn"
                    style="background: #0ea5e9; text-decoration: none; font-size: 14px;">
                    <i class="fas fa-list"></i> View History
                </a>

                <div
                    style="background: #e0f2fe; color: #0369a1; padding: 8px 15px; border-radius: 20px; font-size: 14px; border: 1px solid #bae6fd;">
                    <i class="fas fa-history"></i> Last Issued: <strong>#
                        <?php echo $last_no; ?>
                    </strong>
                    <span style="font-size: 0.9em; opacity: 0.8;">(Date:
                        <?php echo $last_date; ?>)
                    </span>
                    &nbsp;|&nbsp;
                    Next: <strong>#
                        <?php echo $next_receipt_no; ?>
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Receipt Form -->
    <form method="POST" action="">
        <input type="hidden" name="save_receipt" value="1">

        <div class="sheet">
            <!-- School Copy (Source) -->
            <?php render_receipt('school', $next_receipt_no); ?>

            <!-- Student/Donor Copy (Mirror) -->
            <?php render_receipt('donor', $next_receipt_no); ?>
        </div>

        <div class="controls" style="text-align: center; margin-top: 20px;">
            <button type="submit" class="btn btn-print"><i class="fas fa-print"></i> Save & Print Receipt</button>
        </div>
    </form>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </script>
    <script>
        // English Number Conversion Logic
        function convertNumberToEnglish(number) {
            var ns = [
                { value: 10000000, str: "Crore" },
                { value: 100000, str: "Lakh" },
                { value: 1000, str: "Thousand" },
                { value: 100, str: "Hundred" },
                { value: 90, str: "Ninety" },
                { value: 80, str: "Eighty" },
                { value: 70, str: "Seventy" },
                { value: 60, str: "Sixty" },
                { value: 50, str: "Fifty" },
                { value: 40, str: "Forty" },
                { value: 30, str: "Thirty" },
                { value: 20, str: "Twenty" },
                { value: 19, str: "Nineteen" },
                { value: 18, str: "Eighteen" },
                { value: 17, str: "Seventeen" },
                { value: 16, str: "Sixteen" },
                { value: 15, str: "Fifteen" },
                { value: 14, str: "Fourteen" },
                { value: 13, str: "Thirteen" },
                { value: 12, str: "Twelve" },
                { value: 11, str: "Eleven" },
                { value: 10, str: "Ten" },
                { value: 9, str: "Nine" },
                { value: 8, str: "Eight" },
                { value: 7, str: "Seven" },
                { value: 6, str: "Six" },
                { value: 5, str: "Five" },
                { value: 4, str: "Four" },
                { value: 3, str: "Three" },
                { value: 2, str: "Two" },
                { value: 1, str: "One" }
            ];

            var result = '';
            for (var n of ns) {
                if (number >= n.value) {
                    if (number <= 99) {
                        result += n.str;
                        number -= n.value;
                        if (number > 0) result += ' ';
                    } else {
                        var t = Math.floor(number / n.value);
                        var d = number % n.value;
                        if (d > 0) {
                            return convertNumberToEnglish(t) + ' ' + n.str + ' ' + convertNumberToEnglish(d);
                        } else {
                            return convertNumberToEnglish(t) + ' ' + n.str;
                        }
                    }
                }
            }
            return result;
        }

        $(document).ready(function () {
            // Clean URL if just printed
            <?php if (isset($_GET['print_receipt'])): ?>
                window.print();
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.pathname);
                }
            <?php endif; ?>

            // Sync Logic
            $(document).on('input keyup change', '.source-input', function () {
                var field = $(this).data('field');
                var val = $(this).val();

                // Find corresponding mirror input
                $('.mirror-input[data-field="' + field + '"]').val(val);

                // If it's a fee amount, trigger calc
                if ($(this).hasClass('fee-amount')) {
                    calculateTotal();
                }
            });

            // Initial Sync
            $('.source-input').each(function () {
                $(this).trigger('change');
            });
        });

        function calculateTotal() {
            let total = 0;
            $('.receipt-school .fee-amount').each(function () {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) total += val;
            });

            let totalStr = total.toFixed(2);
            $('#grandTotal_school').text(totalStr);
            $('#grandTotal_donor').text(totalStr);

            // Convert to Words
            let words = "";
            let whole = Math.floor(total);
            let decimal = Math.round((total - whole) * 100);

            if (whole > 0) words += convertNumberToEnglish(whole) + " Rupees";
            if (decimal > 0) {
                if (words !== "") words += " and ";
                words += convertNumberToEnglish(decimal) + " Paisa";
            }
            if (words !== "") words += " Only";

            $('.source-input[data-field="words"]').val(words).trigger('change'); // Trigger change to sync
        }
    </script>

</body>

</html>