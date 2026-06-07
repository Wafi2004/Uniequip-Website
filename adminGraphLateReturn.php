<?php 
session_start();
include 'db.php'; 

// Redirect if not admin
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Duration Analysis - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .duration-table {
            width: 100%;
            border-collapse: collapse;
        }

        .duration-table th,
        .duration-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
            vertical-align: top;
        }

        .duration-table th {
            background-color: var(--bg-primary);
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .duration-table ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .duration-table li {
            padding: 2px 0;
            font-size: 13px;
            color: var(--text-primary);
        }

        .duration-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            background-color: var(--warning-color);
            color: white;
            border-radius: var(--radius-sm);
            font-size: 11px;
            font-weight: 600;
        }

        .month-header {
            background: linear-gradient(135deg, var(--primary-color), #4338ca);
            color: white;
        }

        .club-cell {
            background-color: var(--bg-secondary);
            font-weight: 500;
        }
        
        /* Print Styles */
        @media print {
            .sidebar, .top-header, .breadcrumb, .header-actions, .user-menu {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .content-area {
                padding: 20px !important;
            }
            
            .page-title {
                text-align: center;
                margin-bottom: 20px;
                font-size: 24px;
                color: #000;
            }
            
            .table-container {
                box-shadow: none !important;
                border: 1px solid #000;
            }
            
            .table-header {
                background-color: #f5f5f5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .duration-table th {
                background-color: #e5e5e5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 1px solid #000;
            }
            
            .duration-table td {
                border: 1px solid #000;
            }
            
            .month-header {
                background-color: #d1d5db !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .club-cell {
                background-color: #f9fafb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .action-buttons {
                display: none !important;
            }
            
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
                font-size: 18px;
                font-weight: bold;
            }
            
            body {
                font-size: 12px;
                line-height: 1.4;
            }
        }
        
        .print-header {
            display: none;
        }
        
        .print-btn {
            background-color: #059669;
            color: white;
            border: none;
        }
        
        .print-btn:hover {
            background-color: #047857;
        }

        /* Logout Modal Animations */
        .modal-overlay {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            transform: scale(0.7) translateY(-50px);
            transition: transform 0.3s ease;
        }

        .modal-overlay.show .modal-content {
            transform: scale(1) translateY(0);
        }
    </style>
    <script>
        function showLogoutConfirm() {
            const modal = document.getElementById('logoutModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        function hideLogoutConfirm() {
            const modal = document.getElementById('logoutModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function confirmLogout() {
            window.location.href = 'logout.php';
        }
        
        function printReport() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">UE</div>
                <div class="sidebar-title">UniEquip Dashboard</div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-item">
                        <form action="admin_dashboard.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link">
                                <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </button>
                        </form>
                    </div>
                    <div class="nav-item">
                        <form action="admin_view_equipment.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link">
                                <div class="nav-icon"><i class="fas fa-tools"></i></div>
                                View Equipment
                            </button>
                        </form>
                    </div>
                    <div class="nav-item">
                        <form action="admin_view_all_record.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link">
                                <div class="nav-icon"><i class="fas fa-clipboard-list"></i></div>
                                Booking Records
                            </button>
                        </form>
                    </div>
                    <div class="nav-item">
                        <form action="claim_equipment.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link">
                                <div class="nav-icon"><i class="fas fa-hand-holding"></i></div>
                                Claim Equipment
                            </button>
                        </form>
                    </div>
                    <div class="nav-item">
                        <form action="return_equipment.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link">
                                <div class="nav-icon"><i class="fas fa-undo"></i></div>
                                Return Equipment
                            </button>
                        </form>
                    </div>
                    <div class="nav-item">
                        <form action="view_club.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link">
                                <div class="nav-icon"><i class="fas fa-users"></i></div>
                                Club Details
                            </button>
                        </form>
                    </div>
                    <div class="nav-item">
                        <form action="report.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link active">
                                <div class="nav-icon"><i class="fas fa-chart-bar"></i></div>
                                Reports
                            </button>
                        </form>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">ACCOUNT PAGES</div>
                    <div class="nav-item">
                        <form action="admin_profile.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link">
                                <div class="nav-icon"><i class="fas fa-user"></i></div>
                                Profile
                            </button>
                        </form>
                    </div>
                    <div class="nav-item">
                        <form action="rgstaff.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link">
                                <div class="nav-icon"><i class="fas fa-user-plus"></i></div>
                                Register Admin
                            </button>
                        </form>
                    </div>
                </div>

                <div style="margin-top: auto; padding: 16px;">
                    <div style="background: #4f46e5; border-radius: 12px; padding: 16px; text-align: center; color: white;">
                        <i class="fas fa-power-off" style="font-size: 24px; margin-bottom: 8px;"></i>
                        <div style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Ready to leave?</div>
                        <div style="font-size: 10px; margin-bottom: 12px;">Click logout to exit</div>
                        <button onclick="showLogoutConfirm()" class="btn btn-primary btn-sm" style="background: white; color: #4f46e5; width: 100%; border: none; padding: 8px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <div class="top-header">
                <div class="header-left">
                    <div class="breadcrumb">
                        <i class="fas fa-home"></i>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item">Management</span>
                        <span class="breadcrumb-separator">/</span>
                        <a href="report.php" class="breadcrumb-item">Reports</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Booking Duration Analysis Report</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-actions">
                        <button onclick="showLogoutConfirm()" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                        <div class="user-menu">
                            <div class="user-avatar"><?php echo strtoupper(substr($staff_name, 0, 2)); ?></div>
                            <div class="user-info">
                                <div class="user-name"><?php echo $staff_name; ?></div>
                                <div class="user-role"><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Print Header (only visible when printing) -->
                <div class="print-header">
                    <h1>UniEquip - Booking Duration Analysis Report</h1>
                    <p>Generated on: <?php echo date('F d, Y'); ?></p>
                </div>
                
                <!-- Report Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-clock" style="margin-right: 8px; color: var(--warning-color);"></i>
                            Top Clubs By Booking Durations (By Month)
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-info-circle"></i>
                            Shows longest booking durations per month
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table class="duration-table">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Year</th>
                                    <th style="width: 100px;">Month</th>
                                    <th style="width: 150px;">Club Name(s)</th>
                                    <th>Item Name(s)</th>
                                    <th>Event Name(s)</th>
                                    <th style="width: 180px;">Start–End Date(s)</th>
                                    <th style="width: 100px;">Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "
                                    SELECT
                                        YEAR(b.start_date) AS year,
                                        MONTH(b.start_date) AS month,
                                        b.club_name,
                                        b.event_name,
                                        e.name AS item_name,
                                        b.start_date,
                                        b.end_date,
                                        DATEDIFF(b.end_date, b.start_date) AS duration
                                    FROM booking b
                                    JOIN booking_equipment be ON b.id_booking = be.id_booking
                                    JOIN equipment e ON be.id_equipment = e.id_equipment
                                    WHERE b.start_date IS NOT NULL AND b.end_date IS NOT NULL
                                        AND b.status IN ('approved', 'borrowed', 'not claimed', 'returned')
                                ";

                                $result = $connect->query($sql);
                                $data_by_year_month = [];

                                while ($row = $result->fetch_assoc()) {
                                    $year = $row['year'];
                                    $month = $row['month'];
                                    $duration = $row['duration'];
                                    $key = $year . '-' . $month;

                                    if (!isset($data_by_year_month[$key])) {
                                        $data_by_year_month[$key] = [
                                            'year' => $year,
                                            'month' => $month,
                                            'max_duration' => $duration,
                                            'records' => [$row]
                                        ];
                                    } else {
                                        if ($duration > $data_by_year_month[$key]['max_duration']) {
                                            $data_by_year_month[$key]['max_duration'] = $duration;
                                            $data_by_year_month[$key]['records'] = [$row];
                                        } elseif ($duration == $data_by_year_month[$key]['max_duration']) {
                                            $data_by_year_month[$key]['records'][] = $row;
                                        }
                                    }
                                }

                                // Group records by club and then by event+date combination for each year-month
                                foreach ($data_by_year_month as $year_month_key => $data) {
                                    $records = $data['records'];
                                    $duration = $data['max_duration'];
                                    $year = $data['year'];
                                    $month = $data['month'];
                                    $month_name = date("F", mktime(0, 0, 0, $month, 10));
                                    
                                    // Group records by club, then by event+date combination
                                    $clubs_data = [];
                                    foreach ($records as $r) {
                                        $club = $r['club_name'];
                                        $event = $r['event_name'];
                                        $date_range = htmlspecialchars($r['start_date']) . " – " . htmlspecialchars($r['end_date']);
                                        $event_date_key = $event . "|" . $date_range;
                                        
                                        if (!isset($clubs_data[$club])) {
                                            $clubs_data[$club] = [];
                                        }
                                        if (!isset($clubs_data[$club][$event_date_key])) {
                                            $clubs_data[$club][$event_date_key] = [
                                                'event' => htmlspecialchars($event),
                                                'date_range' => $date_range,
                                                'items' => []
                                            ];
                                        }
                                        $clubs_data[$club][$event_date_key]['items'][] = htmlspecialchars($r['item_name']);
                                    }

                                    // Remove duplicate items for each event+date combination
                                    foreach ($clubs_data as $club => $event_dates) {
                                        foreach ($event_dates as $key => $data_item) {
                                            $clubs_data[$club][$key]['items'] = array_unique($data_item['items']);
                                        }
                                    }

                                    // Calculate total rows needed for this year-month
                                    $total_rows = 0;
                                    foreach ($clubs_data as $club => $event_dates) {
                                        $total_rows += count($event_dates);
                                    }

                                    $year_printed = false;
                                    $month_printed = false;
                                    $duration_printed = false;

                                    foreach ($clubs_data as $club => $event_dates) {
                                        $club_rows = count($event_dates);
                                        $club_printed = false;
                                        
                                        foreach ($event_dates as $event_date_data) {
                                            echo "<tr>";
                                            
                                            // Year cell with rowspan for all rows in this year-month
                                            if (!$year_printed) {
                                                echo "<td rowspan='" . $total_rows . "' class='month-header' style='text-align: center; font-weight: 600;'>" . $year . "</td>";
                                                $year_printed = true;
                                            }
                                            
                                            // Month cell with rowspan for all rows in this year-month
                                            if (!$month_printed) {
                                                echo "<td rowspan='" . $total_rows . "' class='month-header' style='text-align: center; font-weight: 600;'>" . $month_name . "</td>";
                                                $month_printed = true;
                                            }
                                            
                                            // Club cell with rowspan for all events/dates of this club
                                            if (!$club_printed) {
                                                echo "<td rowspan='" . $club_rows . "' class='club-cell'>" . htmlspecialchars($club) . "</td>";
                                                $club_printed = true;
                                            }
                                            
                                            echo "<td><ul><li>" . implode("</li><li>", $event_date_data['items']) . "</li></ul></td>";
                                            echo "<td>" . $event_date_data['event'] . "</td>";
                                            echo "<td style='font-size: 12px; font-family: monospace;'>" . $event_date_data['date_range'] . "</td>";
                                            
                                            // Duration cell with rowspan for all rows in this year-month
                                            if (!$duration_printed) {
                                                echo "<td rowspan='" . $total_rows . "' style='text-align: center;'>";
                                                echo "<span class='duration-badge'>";
                                                echo "<i class='fas fa-clock' style='margin-right: 4px;'></i>";
                                                echo $duration . " day" . ($duration !== 1 ? 's' : '');
                                                echo "</span>";
                                                echo "</td>";
                                                $duration_printed = true;
                                            }
                                            
                                            echo "</tr>";
                                        }
                                    }
                                }

                                if (empty($data_by_year_month)) {
                                    echo "<tr><td colspan='7' style='text-align: center; padding: 40px; color: var(--text-muted);'>";
                                    echo "<i class='fas fa-clock' style='font-size: 48px; margin-bottom: 16px; opacity: 0.3; display: block;'></i>";
                                    echo "<h3>No Duration Data Found</h3>";
                                    echo "<p>There are currently no booking records with duration information.</p>";
                                    echo "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons" style="display: flex; gap: 12px; margin-top: 24px;">
                    <button onclick="printReport()" class="btn print-btn">
                        <i class="fas fa-print"></i>
                        Print Report
                    </button>
                    <form action="report.php" method="post" style="margin: 0;">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Reports
                        </button>
                    </form>
                    <form action="admin_dashboard.php" method="post" style="margin: 0;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 10000; justify-content: center; align-items: center;">
        <div class="modal-content" style="background-color: white; border-radius: 12px; padding: 24px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);">
            <div style="margin-bottom: 16px;">
                <i class="fas fa-sign-out-alt" style="font-size: 48px; color: #ef4444; margin-bottom: 16px;"></i>
                <h3 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 8px;">Confirm Logout</h3>
                <p style="color: #6b7280; font-size: 14px;">Are you sure you want to logout from your account?</p>
            </div>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button onclick="hideLogoutConfirm()" style="padding: 10px 20px; border: 1px solid #d1d5db; background-color: white; color: #374151; border-radius: 6px; cursor: pointer; font-weight: 500;">
                    Cancel
                </button>
                <button onclick="confirmLogout()" style="padding: 10px 20px; background-color: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                    Yes, Logout
                </button>
            </div>
        </div>
    </div>
</body>
</html>