<?php
session_start();
include("db.php");

// Redirect if not admin
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User";

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Base query
$query = "
    SELECT 
        b.id_booking, b.event_name, b.status, b.start_date, b.end_date, 
        b.club_name, b.stud_num, b.return_date,
        u.stud_name
    FROM booking b
    JOIN user u ON b.stud_num = u.stud_num
";

// Filter by status
if (!empty($filter_status)) {
    $query .= " WHERE b.status = ? ORDER BY b.start_date ASC";
    $stmt = $connect->prepare($query);
    if (!$stmt) {
        die("Query error: " . $connect->error);
    }
    $stmt->bind_param("s", $filter_status);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query .= " ORDER BY CASE WHEN b.status = 'pending' THEN 0 ELSE 1 END, b.start_date ASC";
    $result = $connect->query($query);
    if (!$result) {
        die("Query error: " . $connect->error);
    }
}

$statuses = ["pending", "approved", "rejected"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Records - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Logout Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 10px 10px -5px rgb(0 0 0 / 0.04);
            max-width: 400px;
            width: 90%;
            text-align: center;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: white;
            font-size: 24px;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .modal-message {
            font-size: 16px;
            color: var(--text-secondary);
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .modal-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 100px;
        }

        .modal-btn-cancel {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .modal-btn-cancel:hover {
            background-color: var(--bg-primary);
        }

        .modal-btn-confirm {
            background-color: #ef4444;
            color: white;
        }

        .modal-btn-confirm:hover {
            background-color: #dc2626;
        }

        /* Status-specific badges */
        .status-pending {
            background-color: rgb(245 158 11 / 0.1);
            color: var(--warning-color);
        }

        .status-approved {
            background-color: rgb(16 185 129 / 0.1);
            color: var(--accent-color);
        }

        .status-rejected {
            background-color: rgb(239 68 68 / 0.1);
            color: var(--error-color);
        }

        /* Action form styling */
        .action-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .action-select {
            padding: 4px 8px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 12px;
            background-color: var(--bg-secondary);
        }

        .action-btn {
            padding: 4px 12px;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: var(--primary-color);
            color: white;
        }

        .action-btn:hover {
            background-color: #4338ca;
        }
    </style>
    <script>
        function confirmLogout() {
            showLogoutModal();
            return false;
        }

        function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        function proceedLogout() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'logout.php';
            document.body.appendChild(form);
            form.submit();
        }

        window.onclick = function(event) {
            const logoutModal = document.getElementById('logoutModal');
            if (event.target === logoutModal) {
                hideLogoutModal();
            }
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
                            <button type="submit" class="nav-link active">
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
                            <button type="submit" class="nav-link">
                                <div class="nav-icon"><i class="fas fa-clipboard-list"></i></div>
                                Report
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
                        <form action="logout.php" method="post" style="margin: 0;" onsubmit="return confirmLogout()">
                            <button type="submit" class="btn btn-primary btn-sm" style="background: white; color: #4f46e5; width: 100%; border: none; padding: 8px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                                Logout
                            </button>
                        </form>
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
                        <span class="breadcrumb-item active">Booking Records</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-actions">
                        <form action="logout.php" method="post" style="margin: 0;" onsubmit="return confirmLogout()">
                            <button type="submit" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </button>
                        </form>
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
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">
                            <i class="fas fa-filter" style="margin-right: 6px; color: var(--primary-color);"></i>
                            Filter by Status
                        </label>
                        <form method="get" action="admin_view_all_record.php" style="display: flex; gap: 12px; align-items: end;">
                            <select name="status" class="form-select" style="min-width: 200px;" onchange="this.form.submit()">
                                <option value="">-- Show All Bookings --</option>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php if ($filter_status === $status) echo 'selected'; ?>>
                                        <?php echo ucfirst($status); ?> Bookings
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($filter_status)): ?>
                                <a href="admin_view_all_record.php" class="btn btn-secondary" style="min-width: 150px; height: 35px;">
                                    <i class="fas fa-times"></i>
                                    Clear Filter
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Booking Records Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-clipboard-list" style="margin-right: 8px; color: var(--primary-color);"></i>
                            Equipment Booking Records
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-database"></i>
                            <?php echo $result->num_rows; ?> record<?php echo $result->num_rows !== 1 ? 's' : ''; ?> found
                            <?php if ($filter_status): ?>
                                <span style="margin-left: 8px; padding: 2px 8px; background-color: var(--bg-primary); border-radius: var(--radius-sm); font-size: 12px;">
                                    Status: <?php echo ucfirst($filter_status); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($result->num_rows > 0): ?>
                        <div class="table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Event Name</th>
                                        <th>Student</th>
                                        <th>Student ID</th>
                                        <th>Club</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Processed By</th>
                                        <th>Status</th>
                                        <th>Details</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <span style="font-family: monospace; font-weight: 600; color: var(--primary-color);">
                                                    #<?php echo htmlspecialchars($row['id_booking']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="font-weight: 500;">
                                                    <?php echo htmlspecialchars($row['event_name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['stud_name']); ?></td>
                                            <td>
                                                <span style="font-family: monospace; font-size: 12px; color: var(--text-secondary);">
                                                    <?php echo htmlspecialchars($row['stud_num']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['club_name']); ?></td>
                                            <td>
                                                <span style="font-size: 12px;">
                                                    <?php echo date('M d, Y', strtotime($row['start_date'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span style="font-size: 12px;">
                                                    <?php echo date('M d, Y', strtotime($row['end_date'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['staff_num'])): ?>
                                                    <span style="font-size: 12px;">
                                                        <?php 
                                                        // Display staff name if available, otherwise show staff number
                                                        echo !empty($row['processed_by']) 
                                                            ? htmlspecialchars($row['processed_by']) 
                                                            : 'Staff #' . htmlspecialchars($row['staff_num']);
                                                        ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color: var(--text-muted); font-size: 12px;">
                                                        Not processed
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $row['status']; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="booking_detail.php?id=<?php echo $row['id_booking']; ?>" class="btn btn-view btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                    View
                                                </a>
                                            </td>
                                            <td>
                                                <form method="post" action="admin_update_booking.php" class="action-form">
                                                    <input type="hidden" name="id_booking" value="<?php echo $row['id_booking']; ?>">
                                                    <select name="new_status" class="action-select">
                                                        <?php foreach ($statuses as $status): ?>
                                                            <option value="<?php echo $status; ?>" <?php if ($row['status'] === $status) echo 'selected'; ?>>
                                                                <?php echo ucfirst($status); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="action-btn">
                                                        <i class="fas fa-check" style="font-size: 10px;"></i>
                                                        Update
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <h3>No Booking Records Found</h3>
                            <p>
                                <?php if ($filter_status): ?>
                                    No bookings found with status "<?php echo htmlspecialchars($filter_status); ?>".
                                    <br>Try adjusting your filter or <a href="admin_view_all_record.php" style="color: var(--primary-color);">view all bookings</a>.
                                <?php else: ?>
                                    There are currently no booking records in the system.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Back to Dashboard -->
                <div style="margin-top: 24px;">
                    <form action="admin_dashboard.php" method="post" style="margin: 0;">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal-overlay" onclick="if(event.target === this) hideLogoutModal()">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-power-off"></i>
            </div>
            <h2 class="modal-title">Logout</h2>
            <p class="modal-message">Are you sure you want to logout?</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" onclick="hideLogoutModal()">Cancel</button>
                <button class="modal-btn modal-btn-confirm" onclick="proceedLogout()">Logout</button>
            </div>
        </div>
    </div>
</body>
</html>
