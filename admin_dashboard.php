<?php
include("db.php");
session_start();

// Redirect to login if not logged in or not admin
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
$query = "SELECT COUNT(id_equipment) AS total FROM equipment";
$result = mysqli_query($connect, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $equipment_count = $row['total'];
}

$total_bookings = 0;
$query = "SELECT COUNT(id_booking) AS total FROM booking WHERE status IN ('Approved', 'Pending', 'Borrowed')";
$result = mysqli_query($connect, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $total_bookings = $row['total'];
}

$active_clubs = 0;
$query = "SELECT COUNT(club_name) AS total FROM club WHERE status IN ('Active', 'active')";
$result = mysqli_query($connect, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $active_clubs = $row['total'];
}


// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User"; // Default fallback
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script>
        function confirmLogout() {
            showLogoutModal();
            return false; // Prevent form submission initially
        }

        function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        function proceedLogout() {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'logout.php';
            document.body.appendChild(form);
            form.submit();
        }
    </script>
    <style>
        /* Custom Modal Styles */
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
    </style>
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
                        <button class="nav-link active">
                            <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </button>
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
                        <span class="breadcrumb-item active">Dashboard</span>
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
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon blue">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="stat-change positive">+15%</div>
                        </div>
                        
                         <div class="stat-number"><?php echo $equipment_count; ?></div>
                        <div class="stat-label">Total Equipment</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon blue">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-change positive">+8%</div>
                        </div>
                        <div class="stat-number"><?php echo $total_bookings;?> </div>
                        <div class="stat-label">Active Bookings</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon blue">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-change positive">+12%</div>
                        </div>
                        <div class="stat-number"><?php echo $active_clubs;?> </div>
                        <div class="stat-label">Registered Clubs</div>
                    </div>
                </div>

                <!-- Quick Actions Grid -->
                <div class="dashboard-grid">
                    <!-- Equipment Management -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Equipment Management</h3>
                                <p style="color: var(--text-secondary); font-size: 14px;">Manage university equipment inventory and availability</p>
                            </div>
                        </div>
                        <div class="card-content">
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <form action="admin_view_equipment.php" method="post" style="margin: 0;">
                                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                        <i class="fas fa-tools"></i>
                                        View Equipment
                                    </button>
                                </form>
                                <form action="admin_view_all_record.php" method="get" style="margin: 0;">
                                    <input type="hidden" name="status" value="pending">
                                    <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                                        <i class="fas fa-clipboard-list"></i>
                                        Approve Booking Records
                                    </button>
                                </form>
                                <form action="claim_equipment.php" method="post" style="margin: 0;">
                                    <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                                        <i class="fas fa-clipboard-list"></i>
                                        Claim Equipment Booking
                                    </button>
                                </form>
                                <form action="return_equipment.php" method="post" style="margin: 0;">
                                    <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                                        <i class="fas fa-clipboard-list"></i>
                                        Return Equipment Booking
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- System Administration -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">System Administration</h3>
                        </div>
                        <div class="card-content">
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <form action="rgstaff.php" method="post" style="margin: 0;">
                                    <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                                        <i class="fas fa-user-plus"></i>
                                        Register New Admin
                                    </button>
                                </form>
                                <form action="view_club.php" method="post" style="margin: 0;">
                                    <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                                        <i class="fas fa-users"></i>
                                        View Club Details
                                    </button>
                                </form>
                                <form action="admin_profile.php" method="post" style="margin: 0;">
                                    <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                                        <i class="fas fa-user"></i>
                                        View Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Equipment Activity</h3>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                +12% this week
                            </div>
                        </div>
                        <div class="card-content">
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; font-size: 14px;">New equipment added</div>
                                        <div style="font-size: 12px; color: var(--text-muted);">2 hours ago</div>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 8px; height: 8px; background: #3b82f6; border-radius: 50%;"></div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; font-size: 14px;">Booking approved</div>
                                        <div style="font-size: 12px; color: var(--text-muted);">4 hours ago</div>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 8px; height: 8px; background: #f59e0b; border-radius: 50%;"></div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; font-size: 14px;">Equipment maintenance scheduled</div>
                                        <div style="font-size: 12px; color: var(--text-muted);">6 hours ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Overview -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">System Overview</h3>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                All systems operational
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="chart-placeholder" style="height: 200px;">
                                <div>
                                    <i class="fas fa-chart-bar"></i>
                                    <p>Equipment usage analytics</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal-overlay" onclick="if(event.target === this) hideLogoutModal()">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h2 class="modal-title">Confirm Logout</h2>
            <p class="modal-message">
                Are you sure you want to logout? You will need to login again to access the dashboard.
            </p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" onclick="hideLogoutModal()">
                    Cancel
                </button>
                <button class="modal-btn modal-btn-confirm" onclick="proceedLogout()">
                    Yes, Logout
                </button>
            </div>
        </div>
    </div>
</body>
</html>
