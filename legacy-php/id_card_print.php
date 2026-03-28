<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$year = isset($_GET['year']) ? $_GET['year'] : '';
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';

if (empty($year) || empty($user_type)) {
    header("Location: id_card_selector.php");
    exit();
}

// Get school info
$school_sql = "SELECT school_name, address, school_logo, estd_date, phone FROM schools WHERE id = ?";
$school_stmt = $conn->prepare($school_sql);
$school_stmt->execute([$school_id]);
$school_info = $school_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch individuals
$individuals = [];
if ($user_type === 'student') {
    $sql = "SELECT id, full_name, symbol_no as roll_no, class, dob_nepali as dob, parent_contact as phone_number, guardian_contact, address, student_photo as photo FROM students WHERE school_id = ? AND class = ? ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $class]);
    $individuals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "SELECT id, full_name, teacher_type as designation, contact as phone_number, address, pan_no, blood_group, citizenship_no, teacher_photo as photo FROM teachers WHERE school_id = ? ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id]);
    $individuals = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($individuals)) {
    echo "<script>alert('No records found for the selected criteria.'); window.close();</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print ID Cards - Smart विद्यालय</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
        }

        .actions {
            text-align: center;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 10px 20px;
            font-size: 1.1rem;
            color: white;
            background: #4f46e5;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-weight: 600;
        }

        .btn:hover {
            background: #4338ca;
        }

        .id-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            justify-content: center;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* CARD MAIN STRUCTURTE */
        .id-card {
            width: 220px;
            /* CR80 Standard */
            height: 340px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            position: relative;
            box-sizing: border-box;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            color: #1e3a8a;
            page-break-inside: avoid;
        }

        /* TOP BLUE ARCH / HEADER STRIP */
        .top-arc {
            background: #2cb5e8;
            /* Sky blue matching image */
            height: 15px;
            width: 100%;
            border-radius: 10px 10px 0 0;
        }

        .header-content {
            text-align: center;
            padding: 5px 8px 0;
            position: relative;
            background: white;
        }

        .school-logo {
            position: absolute;
            top: 5px;
            left: 5px;
            width: 28px;
            height: 28px;
            object-fit: contain;
            border: 1px solid #dc2626;
            border-radius: 50%;
            padding: 1px;
            background: white;
        }

        .school-title {
            margin-left: 20px;
            /* offset for logo */
            color: #1e3a8a;
            line-height: 1.1;
        }

        .school-name-1 {
            font-size: 0.95rem;
            font-weight: 900;
            letter-spacing: -0.2px;
        }

        .school-name-2 {
            font-size: 0.9rem;
            font-weight: 900;
        }

        .school-estd {
            font-size: 0.6rem;
            font-weight: 700;
            margin-top: 2px;
            color: #1e3a8a;
        }

        .school-address-box {
            font-size: 0.55rem;
            font-weight: 700;
            line-height: 1.3;
            margin-top: 2px;
            color: #1e3a8a;
            white-space: pre-line;
        }

        /* PINK BADGE */
        .badge-wrapper {
            margin-top: 5px;
            text-align: center;
        }

        .badge-pill {
            background: #e82c81;
            /* Pinkish-magenta */
            color: white;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 4px 15px;
            border-radius: 15px;
            display: inline-block;
            letter-spacing: 0.5px;
            border: 1px solid #fff;
        }

        /* PHOTO SECTION */
        .photo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 5px;
            position: relative;
        }

        .student-photo {
            width: 65px;
            height: 75px;
            border: 2px solid #579cd8;
            object-fit: cover;
            background: #e2e8f0;
        }

        .signature-box {
            position: absolute;
            right: 15px;
            bottom: -5px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px dashed #1e3a8a;
            width: 45px;
            margin: 0 auto;
        }

        .signature-text {
            font-size: 0.45rem;
            color: #1e3a8a;
            margin-top: 2px;
            font-weight: 500;
        }

        /* DATA TABLE SECTION */
        .data-section {
            flex: 1;
            padding: 2px 10px 5px;
            margin-top: -2px;
            display: flex;
            align-items: center;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.65rem;
            color: #1e3a8a;
            font-weight: 500;
        }

        .data-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .data-label {
            width: 32%;
            font-weight: 500;
            white-space: nowrap;
        }

        .data-colon {
            width: 3%;
            text-align: center;
        }

        .data-value {
            font-weight: 700;
            width: 65%;
            padding-left: 3px;
        }

        .val-name {
            font-size: 0.73rem;
            font-weight: 900;
        }

        /* BOTTOM ARCH */
        .bottom-arc {
            background: #2cb5e8;
            height: 15px;
            width: 100%;
            margin-top: auto;
            border-radius: 0 0 10px 10px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .actions {
                display: none;
            }

            .id-card {
                border: 1px solid #cbd5e1;
                box-shadow: none;
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="actions">
        <button class="btn" onclick="window.print()"><i class="fas fa-print"></i> Print Cards</button>
    </div>

    <div class="id-container">
        <?php foreach ($individuals as $person): ?>
            <div class="id-card">

                <div class="top-arc"></div>

                <div class="header-content">
                    <?php if (!empty($school_info['school_logo'])): ?>
                        <img src="<?php echo htmlspecialchars($school_info['school_logo']); ?>" class="school-logo" alt="Logo">
                    <?php else: ?>
                        <!-- Blank circle placeholder for logo -->
                        <div class="school-logo"
                            style="display:flex; align-items:center; justify-content:center; font-size:10px;"><i
                                class="fas fa-school" style="color:#1e3a8a;"></i></div>
                    <?php endif; ?>

                    <div class="school-title">
                        <div class="school-name-1">Shree Himalaya</div>
                        <div class="school-name-2">Basic School(1-8)</div>
                    </div>

                    <div class="school-estd">Estd.<?php echo htmlspecialchars($school_info['estd_date'] ?? '2040'); ?></div>

                    <?php
                    $custom_address = $school_info['address'] ?? '';
                    if (empty($custom_address)) {
                        $addr_1 = "Bharatpur Metropolitan City-11, Jagritichowk";
                        $addr_2 = "Chitwan, Nepal";
                    } else {
                        $addr_parts = explode(",", $custom_address);
                        $addr_1 = isset($addr_parts[0]) ? trim($addr_parts[0]) : $custom_address;
                        $addr_2 = isset($addr_parts[1]) ? trim($addr_parts[1]) : "Nepal";
                        // Try to grab more parts creatively to match the 2-line structure if it exists
                        if (count($addr_parts) > 2) {
                            $addr_1 = trim($addr_parts[0]) . ", " . trim($addr_parts[1]);
                            $addr_2 = trim($addr_parts[2]);
                        }
                    }
                    ?>
                    <div class="school-address-box">
                        <?php echo htmlspecialchars($addr_1); ?><br>
                        <?php echo htmlspecialchars($addr_2); ?>
                    </div>
                </div>

                <div class="badge-wrapper">
                    <div class="badge-pill">
                        <?php echo ($user_type === 'student') ? 'STUDENT ID CARD' : 'TEACHER ID CARD'; ?>
                    </div>
                </div>

                <div class="photo-section">
                    <?php
                    $photo_src = (!empty($person['photo'])) ? htmlspecialchars($person['photo']) : 'https://ui-avatars.com/api/?name=' . urlencode($person['full_name']) . '&background=e2e8f0&color=1e3a8a';
                    ?>
                    <img src="<?php echo $photo_src; ?>" class="student-photo" alt="Photo">

                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-text">Head Teacher</div>
                    </div>
                </div>

                <div class="data-section">
                    <table class="data-table">
                        <tr>
                            <td class="data-label">Name</td>
                            <td class="data-colon">:</td>
                            <td class="data-value val-name"><?php echo htmlspecialchars($person['full_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="data-label">Address</td>
                            <td class="data-colon">:</td>
                            <?php
                            // Clean up address layout
                            $addr = $person['address'] ?? '';
                            if (empty($addr)) {
                                $addr = '-';
                            } else {
                                // Make it concise by grabbing distinct parts
                                $aparts = explode(',', $addr);
                                if (count($aparts) > 1) {
                                    $addr = trim(end($aparts));
                                    if (count($aparts) >= 2) {
                                        $addr = trim($aparts[count($aparts) - 2]) . ", " . $addr;
                                    }
                                }
                            }
                            ?>
                            <td class="data-value"><?php echo htmlspecialchars($addr); ?></td>
                        </tr>
                        <?php if ($user_type === 'student'): ?>
                            <tr>
                                <td class="data-label">Class</td>
                                <td class="data-colon">:</td>
                                <td class="data-value"><?php echo htmlspecialchars($person['class'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="data-label">Guardian No.</td>
                                <td class="data-colon">:</td>
                                <?php $g_ph = !empty($person['guardian_contact']) ? $person['guardian_contact'] : $person['phone_number']; ?>
                                <td class="data-value"><?php echo htmlspecialchars($g_ph ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="data-label">H.T. Ph.No.</td>
                                <td class="data-colon">:</td>
                                <td class="data-value"><?php echo htmlspecialchars($school_info['phone'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="data-label">D.O.B</td>
                                <td class="data-colon">:</td>
                                <td class="data-value"><?php echo htmlspecialchars($person['dob'] ?? '-'); ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td class="data-label">Post</td>
                                <td class="data-colon">:</td>
                                <td class="data-value"><?php echo htmlspecialchars($person['designation'] ?? 'Teacher'); ?></td>
                            </tr>
                            <tr>
                                <td class="data-label">Citizen.no.</td>
                                <td class="data-colon">:</td>
                                <td class="data-value"><?php echo htmlspecialchars($person['citizenship_no'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="data-label">Blood Group</td>
                                <td class="data-colon">:</td>
                                <td class="data-value"><?php echo htmlspecialchars($person['blood_group'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="data-label">Mob.No.</td>
                                <td class="data-colon">:</td>
                                <td class="data-value"><?php echo htmlspecialchars($person['phone_number'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="data-label">Pan No.</td>
                                <td class="data-colon">:</td>
                                <td class="data-value"><?php echo htmlspecialchars($person['pan_no'] ?? '-'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <div class="bottom-arc"></div>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>