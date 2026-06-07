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
    <title>Equipment Availability Report - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .availability-table {
            width: 100%;
            border-collapse: collapse;
        }

        .availability-table th,
        .availability-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
            vertical-align: top;
        }

        .availability-table th {
            background-color: var(--bg-primary);
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .equipment-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .equipment-stock {
            font-size: 12px;
            color: var(--text-muted);
        }

        .period-item {
            background-color: var(--bg-primary);
            border-radius: var(--radius-md);
            padding: 12px;
            margin-bottom: 8px;
            border-left: 4px solid var(--error-color);
        }

        .period-item:last-child {
            margin-bottom: 0;
        }

        .period-dates {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 4px;
            font-size: 13px;
        }

        .period-booking {
            font-size: 12px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .booking-qty {
            background-color: var(--error-color);
            color: white;
            padding: 2px 6px;
            border-radius: var(--radius-sm);
            font-size: 10px;
            font-weight: 600;
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
            
            .availability-table th {
                background-color: #e5e5e5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 1px solid #000;
            }
            
            .availability-table td {
                border: 1px solid #000;
            }
            
            .period-item {
                background-color: #f9fafb !important;
                border-left: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .booking-qty {
                background-color: #000 !important;
                color: white !important;
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
                        <span class="breadcrumb-item active">Equipment Availability Report</span>
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
                    <h1>UniEquip - Equipment Availability Report</h1>
                    <p>Generated on: <?php echo date('F d, Y'); ?></p>
                </div>
                
                <!-- Report Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-exclamation-triangle" style="margin-right: 8px; color: var(--error-color);"></i>
                            Unavailable Equipment Report
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-info-circle"></i>
                            Equipment unavailable due to overlapping bookings
                        </div>
                    </div>

                    <?php
                    // Step 1: Get all equipment with their total quantity
                    $equipments = [];
                    $eqResult = $connect->query("SELECT id_equipment, name, qty FROM equipment");
                    while ($row = $eqResult->fetch_assoc()) {
                        $equipments[$row['id_equipment']] = [
                            'name' => $row['name'],
                            'qty' => (int)$row['qty'],
                            'bookings' => []
                        ];
                    }

                    // Step 2: Get all bookings (active)
                    $sql = "
                        SELECT b.id_booking, b.start_date, b.end_date, be.id_equipment, be.qty
                        FROM booking b
                        JOIN booking_equipment be ON b.id_booking = be.id_booking
                        WHERE b.status != 'cancelled'
                        ORDER BY be.id_equipment, b.start_date
                    ";

                    $result = $connect->query($sql);

                    // Step 3: Organize bookings under equipment
                    while ($row = $result->fetch_assoc()) {
                        $equipments[$row['id_equipment']]['bookings'][] = [
                            'start' => $row['start_date'],
                            'end' => $row['end_date'],
                            'qty' => (int)$row['qty'],
                            'id_booking' => $row['id_booking']
                        ];
                    }

                    // Step 4: Check overlapping bookings and find unavailable periods
                    $report = [];
                    foreach ($equipments as $id => $eq) {
                        $bookings = $eq['bookings'];
                        $unavailablePeriods = [];

                        for ($i = 0; $i < count($bookings); $i++) {
                            $start1 = strtotime($bookings[$i]['start']);
                            $end1 = strtotime($bookings[$i]['end']);
                            $sumQty = $bookings[$i]['qty'];

                            for ($j = 0; $j < count($bookings); $j++) {
                                if ($i == $j) continue;
                                $start2 = strtotime($bookings[$j]['start']);
                                $end2 = strtotime($bookings[$j]['end']);

                                // Check for overlap
                                if ($start1 <= $end2 && $end1 >= $start2) {
                                    $sumQty += $bookings[$j]['qty'];
                                }
                            }

                            if ($sumQty >= $eq['qty']) {
                                $unavailablePeriods[] = [
                                    'start' => $bookings[$i]['start'],
                                    'end' => $bookings[$i]['end'],
                                    'booked_qty' => $sumQty
                                ];
                            }
                        }

                        if (!empty($unavailablePeriods)) {
                            $report[] = [
                                'name' => $eq['name'],
                                'qty' => $eq['qty'],
                                'unavailable_periods' => $unavailablePeriods
                            ];
                        }
                    }

                    // Step 5: Output
                    if (!empty($report)): ?>
                        <div class="table-wrapper">
                            <table class="availability-table">
                                <thead>
                                    <tr>
                                        <th style="width: 200px;">Equipment</th>
                                        <th style="width: 100px;">Total Stock</th>
                                        <th>Unavailable Periods</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report as $r): ?>
                                        <tr>
                                            <td>
                                                <div class="equipment-name"><?php echo htmlspecialchars($r['name']); ?></div>
                                                <div class="equipment-stock">
                                                    <i class="fas fa-boxes" style="margin-right: 4px;"></i>
                                                    Total: <?php echo $r['qty']; ?> units
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <span style="font-size: 20px; font-weight: 600; color: var(--primary-color);">
                                                    <?php echo $r['qty']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php foreach ($r['unavailable_periods'] as $p): ?>
                                                    <div class="period-item">
                                                        <div class="period-dates">
                                                            <i class="fas fa-calendar-alt" style="margin-right: 6px; color: var(--error-color);"></i>
                                                            <?php echo date('M d, Y', strtotime($p['start'])); ?> 
                                                            to 
                                                            <?php echo date('M d, Y', strtotime($p['end'])); ?>
                                                        </div>
                                                        <div class="period-booking">
                                                            <span>Booked Quantity:</span>
                                                            <span class="booking-qty"><?php echo $p['booked_qty']; ?> units</span>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Stats -->
                        <div style="background-color: var(--bg-primary); border-radius: var(--radius-lg); padding: 20px; margin-top: 20px;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; text-align: center;">
                                <div>
                                    <div style="font-size: 24px; font-weight: 700; color: var(--error-color);">
                                        <?php echo count($report); ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Affected Equipment
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size: 24px; font-weight: 700; color: var(--warning-color);">
                                        <?php echo array_sum(array_map(function($r) { return count($r['unavailable_periods']); }, $report)); ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Conflict Periods
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size: 24px; font-weight: 700; color: var(--primary-color);">
                                        <?php echo count($equipments); ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Total Equipment
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size: 24px; font-weight: 700; color: var(--accent-color);">
                                        <?php echo round((count($report) / count($equipments)) * 100, 1); ?>%
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Conflict Rate
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <h3>All Equipment Available</h3>
                            <p>No equipment was fully booked during overlapping periods. All items are currently available for booking.</p>
                        </div>
                    <?php endif; ?>
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
