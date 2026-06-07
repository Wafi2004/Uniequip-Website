<?php
session_start();
include "db.php";

// Redirect if not admin
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User";

// Run the SQL query
$sql = "
    SELECT *
    FROM (
        SELECT 
            DATE_FORMAT(b.start_date, '%Y-%m') AS month,
            e.name AS equipment_name,
            SUM(be.qty) AS total_borrowed,
            RANK() OVER (PARTITION BY DATE_FORMAT(b.start_date, '%Y-%m') ORDER BY SUM(be.qty) DESC) AS rank_per_month
        FROM booking_equipment be
        JOIN booking b ON be.id_booking = b.id_booking
        JOIN equipment e ON be.id_equipment = e.id_equipment
        WHERE b.status = 'returned'
        GROUP BY month, be.id_equipment
    ) ranked
    WHERE rank_per_month = 1
    ORDER BY month ASC
";

$result = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Most Booked Equipment - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
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
            
            .data-table th {
                background-color: #e5e5e5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 1px solid #000;
            }
            
            .data-table td {
                border: 1px solid #000;
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
                        <span class="breadcrumb-item active">Most Booked Equipment Report</span>
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
                    <h1>UniEquip - Most Booked Equipment Report</h1>
                    <p>Generated on: <?php echo date('F d, Y'); ?></p>
                </div>
                
                <!-- Report Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-trophy" style="margin-right: 8px; color: var(--warning-color);"></i>
                            Top Borrowed Equipment by Month (Returned Bookings Only)
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-database"></i>
                            <?php echo $result->num_rows; ?> month<?php echo $result->num_rows !== 1 ? 's' : ''; ?> with data
                        </div>
                    </div>

                    <?php if ($result->num_rows === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-trophy"></i>
                            <h3>No Data Available</h3>
                            <p>No data available for returned bookings. Complete some bookings and return equipment to see statistics.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Rank</th>
                                        <th>Month & Year</th>
                                        <th>Equipment Name</th>
                                        <th style="width: 150px;">Total Borrowed</th>
                                        <th style="width: 100px;">Champion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    $max_borrowed = 0;
                                    $all_results = [];
                                    
                                    // First pass to get max value and store results
                                    while ($row = $result->fetch_assoc()) {
                                        $formattedMonth = date("F Y", strtotime($row['month'] . "-01"));
                                        $all_results[] = [
                                            'month' => $formattedMonth,
                                            'raw_month' => $row['month'],
                                            'equipment_name' => $row['equipment_name'],
                                            'total_borrowed' => $row['total_borrowed']
                                        ];
                                        if ($row['total_borrowed'] > $max_borrowed) {
                                            $max_borrowed = $row['total_borrowed'];
                                        }
                                    }
                                    
                                    // Second pass to display with rankings
                                    foreach ($all_results as $index => $row):
                                        $percentage = $max_borrowed > 0 ? ($row['total_borrowed'] / $max_borrowed) * 100 : 0;
                                    ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; justify-content: center;">
                                                    <?php if ($count <= 3): ?>
                                                        <i class="fas fa-medal" style="color: <?php echo $count == 1 ? '#ffd700' : ($count == 2 ? '#c0c0c0' : '#cd7f32'); ?>; margin-right: 4px;"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-hashtag" style="color: var(--text-muted); margin-right: 4px;"></i>
                                                    <?php endif; ?>
                                                    <span style="font-weight: 600;"><?php echo $count; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="font-weight: 500;">
                                                    <?php echo htmlspecialchars($row['month']); ?>
                                                </div>
                                                <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;">
                                                    <?php echo $row['raw_month']; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i class="fas fa-tools" style="color: var(--primary-color);"></i>
                                                    <span style="font-weight: 500;">
                                                        <?php echo htmlspecialchars($row['equipment_name']); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="text-align: center;">
                                                    <span style="font-size: 18px; font-weight: 600; color: var(--primary-color);">
                                                        <?php echo $row['total_borrowed']; ?>
                                                    </span>
                                                    <div style="font-size: 11px; color: var(--text-muted);">units borrowed</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="text-align: center;">
                                                    <?php if ($row['total_borrowed'] == $max_borrowed): ?>
                                                        <i class="fas fa-crown" style="color: #ffd700; font-size: 20px;" title="Overall Champion"></i>
                                                    <?php elseif ($percentage >= 80): ?>
                                                        <i class="fas fa-star" style="color: var(--warning-color); font-size: 16px;" title="High Performer"></i>
                                                    <?php elseif ($percentage >= 50): ?>
                                                        <i class="fas fa-thumbs-up" style="color: var(--accent-color); font-size: 14px;" title="Good Performance"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-circle" style="color: var(--text-muted); font-size: 8px;" title="Standard"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                        $count++;
                                    endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Stats -->
                        <div style="background-color: var(--bg-primary); border-radius: var(--radius-lg); padding: 20px; margin-top: 20px;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; text-align: center;">
                                <div>
                                    <div style="font-size: 24px; font-weight: 700; color: var(--primary-color);">
                                        <?php echo count($all_results); ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Months Tracked
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size: 24px; font-weight: 700; color: var(--accent-color);">
                                        <?php echo array_sum(array_column($all_results, 'total_borrowed')); ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Total Units Borrowed
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size: 24px; font-weight: 700; color: var(--warning-color);">
                                        <?php echo round(array_sum(array_column($all_results, 'total_borrowed')) / count($all_results), 1); ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Average per Month
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size: 24px; font-weight: 700; color: var(--error-color);">
                                        <?php echo $max_borrowed; ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Peak Month Record
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Equipment Info -->
                        <?php if (!empty($all_results)): 
                            $champion_equipment = array_filter($all_results, function($item) use ($max_borrowed) {
                                return $item['total_borrowed'] == $max_borrowed;
                            });
                            $champion = reset($champion_equipment);
                        ?>
                            <div style="background: linear-gradient(135deg, var(--warning-color), #ea580c); border-radius: var(--radius-lg); padding: 20px; margin-top: 20px; color: white; text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 8px;">
                                    <i class="fas fa-crown" style="font-size: 24px;"></i>
                                    <h3 style="margin: 0; font-size: 18px;">Overall Champion Equipment</h3>
                                </div>
                                <div style="font-size: 20px; font-weight: 600; margin-bottom: 4px;">
                                    <?php echo htmlspecialchars($champion['equipment_name']); ?>
                                </div>
                                <div style="font-size: 14px; opacity: 0.9;">
                                    Peak performance in <?php echo $champion['month']; ?> with <?php echo $champion['total_borrowed']; ?> units borrowed
                                </div>
                            </div>
                        <?php endif; ?>
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

<?php
$connect->close();
?>
