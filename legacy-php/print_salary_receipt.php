<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'sanghiya_anudan';
$month = $_GET['month'] ?? date('Y-m'); // e.g., 2082-10

// School details
$stmt = $conn->prepare("SELECT school_name, address FROM users WHERE id = ?");
$stmt->execute([$school_id]);
$school = $stmt->fetch(PDO::FETCH_ASSOC);

// Get Tab Label
$stmt = $conn->prepare("SELECT label FROM salary_topics WHERE school_id = ? AND tab_key = ?");
$stmt->execute([$school_id, $tab]);
$tab_label = $stmt->fetchColumn() ?: 'संघीय अनुदान';

// Get Teachers and Salaries for this tab & month
$query = "
    SELECT 
        t.full_name, 
        t.address, 
        t.qualification, 
        s.total_salary, 
        s.gross_salary, 
        s.tax_deduction, 
        s.payable_amount,
        s.net_salary
    FROM teacher_assign_salary tas
    JOIN teachers t ON tas.teacher_id = t.id
    LEFT JOIN teacher_salaries s ON t.id = s.teacher_id AND s.salary_month = :month AND s.tab_key = :tab
    WHERE tas.school_id = :school_id AND tas.tab_key = :tab
    ORDER BY t.full_name ASC
";
$stmt = $conn->prepare($query);
$stmt->execute([
    'school_id' => $school_id,
    'tab' => $tab,
    'month' => $month
]);
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert YYYY-MM to Nepali Parts
$m_parts = explode('-', $month);
$year = $m_parts[0] ?? '';
$m_num = $m_parts[1] ?? '';

$n_months = [
    '01' => 'वैशाख',
    '02' => 'जेठ',
    '03' => 'असार',
    '04' => 'साउन',
    '05' => 'भदौ',
    '06' => 'असोज',
    '07' => 'कार्तिक',
    '08' => 'मंसिर',
    '09' => 'पुष',
    '10' => 'माघ',
    '11' => 'फागुन',
    '12' => 'चैत'
];
$month_str = $n_months[$m_num] ?? '';

// Convert English numbers to Nepali numerals for the header
function toNepaliNum($num)
{
    $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $nep = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
    return str_replace($eng, $nep, (string) $num);
}
?>
<!DOCTYPE html>
<html lang="ne">

<head>
    <meta charset="UTF-8">
    <title>Salary Receipt -
        <?= htmlspecialchars($tab_label) ?>
    </title>
    <style>
        body {
            font-family: 'Kalimati', 'Noto Sans Devanagari', sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
        }

        .desc {
            font-size: 14px;
            margin-bottom: 10px;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
        }

        th {
            font-weight: bold;
        }

        /* Align specific columns */
        .text-left {
            text-align: left;
            padding-left: 8px;
        }

        .text-right {
            text-align: right;
            padding-right: 8px;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .sig-block {
            text-align: center;
        }

        .sig-line {
            border-bottom: 1px dotted #000;
            width: 200px;
            display: inline-block;
            margin-bottom: 5px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 15mm;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Print
            Document</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Close</button>
    </div>

    <div class="header">
        <h2>श्री
            <?= htmlspecialchars($school['school_name']) ?>
        </h2>
    </div>

    <div class="desc">
        भरपाई दादै तपशिलमा उल्लेखित शिक्षक / कर्मचारी आर्थिक वर्ष
        <?= toNepaliNum($year) ?>/
        <?= toNepaliNum(substr($year + 1, -2)) ?> को
        <?= $month_str ?> महिनाको तलब भत्ता
        <?= htmlspecialchars($tab_label) ?> (स.का र कर्मचारी संघ) को निम्न बमोजिम आफ्नो बैंकको व्यक्तिगत खाता मार्फत
        भरपाई गरिदियौँ
        <?= toNepaliNum($year) ?>/
        <?= toNepaliNum($m_num) ?>/
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">क्र सं</th>
                <th style="width: 180px;">नाम, थर</th>
                <th style="width: 120px;">ठेगाना</th>
                <th style="width: 100px;">योग्यता</th>
                <th>तलव</th>
                <th>कुल जम्मा</th>
                <th>स.सु.कर</th>
                <th>कूल जम्मा</th>
                <th style="width: 150px;">हस्ताक्षर</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $t_salary = 0;
            $t_gross = 0;
            $t_tax = 0;
            $t_net = 0;
            foreach ($teachers as $i => $t):
                $sal = floatval($t['total_salary']);
                $gross = floatval($t['gross_salary']);
                $tax = floatval($t['tax_deduction']);
                $net = floatval($t['payable_amount']) - $tax; // Cool Jamma is Net Payable
            
                $t_salary += $sal;
                $t_gross += $gross;
                $t_tax += $tax;
                $t_net += $net;
                ?>
                <tr>
                    <td>
                        <?= toNepaliNum($i + 1) ?>
                    </td>
                    <td class="text-left"><strong>
                            <?= htmlspecialchars($t['full_name']) ?>
                        </strong></td>
                    <td>
                        <?= htmlspecialchars($t['address'] ?? '') ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($t['qualification'] ?? '') ?>
                    </td>
                    <td class="text-right">
                        <?= toNepaliNum(number_format($sal)) ?>
                    </td>
                    <td class="text-right">
                        <?= toNepaliNum(number_format($gross)) ?>
                    </td>
                    <td class="text-right">
                        <?= toNepaliNum(number_format($tax)) ?>
                    </td>
                    <td class="text-right"><strong>
                            <?= toNepaliNum(number_format($net)) ?>
                        </strong></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
            <!-- Empty Rows for Signing aesthetics (if less than some number, but not strictly needed) -->
            <tr>
                <td colspan="4" class="text-right"><strong>जम्मा</strong></td>
                <td class="text-right" style="color:red;"><strong>
                        <?= toNepaliNum(number_format($t_salary)) ?>
                    </strong></td>
                <td class="text-right" style="color:red;"><strong>
                        <?= toNepaliNum(number_format($t_gross)) ?>
                    </strong></td>
                <td class="text-right" style="color:red;"><strong>
                        <?= toNepaliNum(number_format($t_tax)) ?>
                    </strong></td>
                <td class="text-right" style="color:red;"><strong>
                        <?= toNepaliNum(number_format($t_net)) ?>
                    </strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="sig-block">
            <span class="sig-line"></span><br>
            तयार गर्ने
        </div>
        <div class="sig-block">
            <span class="sig-line"></span><br>
            (.................................)<br>
            प्रधानाध्यापक
        </div>
    </div>
</body>

</html>