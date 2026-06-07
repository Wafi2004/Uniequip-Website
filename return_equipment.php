<?php
session_start();
include("db.php");

// Only admin can access this
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User";

// Handle "Returned" button click
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['return_id'])) {
    $return_id = $_POST['return_id'];
    $today = date('Y-m-d');

    // Update booking status to 'returned' and set return_date to today
    $stmt = $connect->prepare("UPDATE booking SET status = 'returned', return_date = ? WHERE id_booking = ?");
    $stmt->bind_param("si", $today, $return_id);
    $stmt->execute();
    $stmt->close();
    
    // Set success message
    $success_message = "Booking #$return_id has been marked as returned successfully!";
}

// Fetch bookings with status 'borrowed'
$stmt = $connect->prepare("
    SELECT b.id_booking, b.event_name, b.start_date, b.end_date, b.club_name, u.stud_name
    FROM booking b
    JOIN user u ON b.stud_num = u.stud_num
    WHERE b.status = 'borrowed'
    ORDER BY b.start_date ASC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Equipment - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Success Alert */
        .success-alert {
            background-color: rgb(16 185 129 / 0.1);
            border: 1px solid rgb(16 185 129 / 0.3);
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--accent-color);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Status Badge */
        .status-borrowed {
            background-color: rgb(245 158 11 / 0.1);
            color: #d97706;
            padding: 4px 12px;
            border-radius: var(--radius-sm);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Date Info Card */
        .date-info-card {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            color: white;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .date-info-icon {
            font-size: 24px;
        }

        .date-info-content h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .date-info-content p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }

        /* Action Buttons */
        .btn-return {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-return:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .btn-view {
            background-color: rgb(59 130 246 / 0.1);
            color: #3b82f6;
            border: 1px solid rgb(59 130 246 / 0.2);
            padding: 6px 12px;
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-view:hover {
            background-color: rgb(59 130 246 / 0.2);
            border-color: rgb(59 130 246 / 0.3);
            color: #2563eb;
        }

        /* Overdue indicator */
        .overdue-indicator {
            background-color: rgb(239 68 68 / 0.1);
            color: var(--error-color);
            padding: 2px 8px;
            border-radius: var(--radius-sm);
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-left: 8px;
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

        function confirmReturn(bookingId) {
            return confirm(`Are you sure you want to mark booking #${bookingId} as returned?\n\nThis action will update the booking status and record today's date as the return date.`);
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
                            <button type="submit" class="nav-link active">
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
                            <button type="submit" class="nav-link">
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
                        <span class="breadcrumb-item active">Return Equipment</span>
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
                <?php if (isset($success_message)): ?>
                    <div class="success-alert">
                        <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                        <div>
                            <strong>Success!</strong> <?php echo $success_message; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Today's Date Info -->
                <div class="date-info-card">
                    <i class="fas fa-calendar-day date-info-icon"></i>
                    <div class="date-info-content">
                        <h3>Processing Returns for Today</h3>
                        <p><?php echo date('l, F j, Y'); ?> - All returns will be recorded with today's date</p>
                    </div>
                </div>

                <!-- Equipment Returns Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-undo" style="margin-right: 8px; color: var(--warning-color);"></i>
                            Borrowed Equipment - Ready for Return
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-database"></i>
                            <?php echo $result->num_rows; ?> booking<?php echo $result->num_rows !== 1 ? 's' : ''; ?> currently borrowed
                        </div>
                    </div>

                    <?php if ($result->num_rows === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-undo"></i>
                            <h3>No Borrowed Equipment</h3>
                            <p>There are currently no borrowed equipment items waiting for return.</p>
                            <div style="margin-top: 20px;">
                                <form action="claim_equipment.php" method="post" style="margin: 0;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-hand-holding"></i>
                                        Manage Equipment Claims
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Booking ID</th>
                                        <th>Event Name</th>
                                        <th>Club</th>
                                        <th style="width: 120px;">Start Date</th>
                                        <th style="width: 120px;">End Date</th>
                                        <th>Borrowed By</th>
                                        <th style="width: 100px;">Status</th>
                                        <th style="width: 120px;">Equipment</th>
                                        <th style="width: 120px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <?php
                                        $today = date('Y-m-d');
                                        $endDate = $row['end_date'];
                                        $isOverdue = $endDate < $today;
                                        ?>
                                        <tr>
                                            <td>
                                                <span style="font-weight: 600; color: var(--primary-color);">
                                                    #<?= $row['id_booking'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="font-weight: 500;">
                                                    <?= htmlspecialchars($row['event_name']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i class="fas fa-users" style="color: var(--text-muted); font-size: 12px;"></i>
                                                    <?= htmlspecialchars($row['club_name']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="font-size: 13px;">
                                                    <?= date('M d, Y', strtotime($row['start_date'])) ?>
                                                </div>
                                                <div style="font-size: 11px; color: var(--text-muted);">
                                                    <?= date('l', strtotime($row['start_date'])) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="font-size: 13px;">
                                                    <?= date('M d, Y', strtotime($row['end_date'])) ?>
                                                    <?php if ($isOverdue): ?>
                                                        <span class="overdue-indicator">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Overdue
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-size: 11px; color: var(--text-muted);">
                                                    <?= date('l', strtotime($row['end_date'])) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i class="fas fa-user" style="color: var(--text-muted); font-size: 12px;"></i>
                                                    <?= htmlspecialchars($row['stud_name']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-borrowed">
                                                    <i class="fas fa-clock" style="margin-right: 4px;"></i>
                                                    Borrowed
                                                </span>
                                            </td>
                                            <td>
                                                <a href="return_view_equipment.php?id_booking=<?= $row['id_booking'] ?>" class="btn-view">
                                                    <i class="fas fa-eye"></i>
                                                    View Items
                                                </a>
                                            </td>
                                            <td>
                                                <form method="post" style="margin: 0;">
                                                    <input type="hidden" name="return_id" value="<?= $row['id_booking'] ?>">
                                                    <button type="submit" class="btn-return" onclick="return confirmReturn(<?= $row['id_booking'] ?>)">
                                                        <i class="fas fa-undo"></i>
                                                        Mark Returned
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <form action="admin_dashboard.php" method="post" style="margin: 0;">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </button>
                    </form>
                    <form action="admin_view_all_record.php" method="post" style="margin: 0;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-clipboard-list"></i>
                            View All Records
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
