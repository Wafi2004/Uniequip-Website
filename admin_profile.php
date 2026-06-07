<?php
session_start();
include("db.php");

// Debugging (optional, remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User";
$staff_num = $_SESSION['user_number'];

// Retrieve admin details from database
$query = "SELECT * FROM admin WHERE staff_num = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("s", $staff_num);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
} else {
    echo "<script>alert('Admin profile not found.'); window.location.href='login.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Profile Card Styles */
        .profile-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), #6366f1);
            padding: 40px 32px;
            text-align: center;
            color: white;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 48px;
            font-weight: 700;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .profile-role {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 32px;
        }

        .profile-stat {
            text-align: center;
        }

        .profile-stat-number {
            font-size: 24px;
            font-weight: 700;
            display: block;
        }

        .profile-stat-label {
            font-size: 12px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .profile-body {
            padding: 32px;
        }

        .profile-section {
            margin-bottom: 32px;
        }

        .profile-section:last-child {
            margin-bottom: 0;
        }

        .profile-section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .profile-field {
            display: flex;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .profile-field:last-child {
            border-bottom: none;
        }

        .profile-field-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            background: var(--bg-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            margin-right: 16px;
            font-size: 16px;
        }

        .profile-field-content {
            flex: 1;
        }

        .profile-field-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .profile-field-value {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .profile-actions {
            display: flex;
            gap: 12px;
            padding-top: 24px;
            border-top: 1px solid var(--border-light);
        }

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
                        <button class="nav-link active">
                            <div class="nav-icon"><i class="fas fa-user"></i></div>
                            Profile
                        </button>
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
                        <span class="breadcrumb-item active">Profile</span>
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
                <!-- Profile Information -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-user-circle" style="margin-right: 8px; color: var(--primary-color);"></i>
                            Administrator Profile
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-info-circle"></i>
                            Your account information
                        </div>
                    </div>

                    <div style="padding: 32px;">
                        <!-- Profile Header -->
                        <div style="text-align: center; margin-bottom: 40px; padding: 32px; background: linear-gradient(135deg, var(--primary-color), #6366f1); border-radius: 16px; color: white;">
                            <div style="width: 120px; height: 120px; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 48px; font-weight: 700; border: 4px solid rgba(255, 255, 255, 0.3);">
                                <?php echo strtoupper(substr($admin['staff_name'], 0, 2)); ?>
                            </div>
                            <h3 style="font-size: 28px; font-weight: 700; margin-bottom: 8px;"><?php echo htmlspecialchars($admin['staff_name']); ?></h3>
                            <p style="font-size: 16px; opacity: 0.9; margin-bottom: 20px;">System Administrator</p>
                            <div style="display: flex; justify-content: center; gap: 32px;">
                                <div style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: 700;">Active</div>
                                    <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Status</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: 700;">Admin</div>
                                    <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Role</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: 700;">2024</div>
                                    <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Since</div>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Fields -->
                        <div style="display: grid; gap: 24px; max-width: 600px; margin: 0 auto;">
                            <!-- Personal Information Section -->
                            <div>
                                <h4 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-user"></i>
                                    Personal Information
                                </h4>
                                
                                <div style="display: flex; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-light);">
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--bg-primary); display: flex; align-items: center; justify-content: center; color: var(--primary-color); margin-right: 16px;">
                                        <i class="fas fa-id-badge"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Staff Number</div>
                                        <div style="font-size: 16px; font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($admin['staff_num']); ?></div>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-light);">
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--bg-primary); display: flex; align-items: center; justify-content: center; color: var(--primary-color); margin-right: 16px;">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Full Name</div>
                                        <div style="font-size: 16px; font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($admin['staff_name']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information Section -->
                            <div>
                                <h4 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-address-book"></i>
                                    Contact Information
                                </h4>
                                
                                <div style="display: flex; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-light);">
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--bg-primary); display: flex; align-items: center; justify-content: center; color: var(--primary-color); margin-right: 16px;">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Phone Number</div>
                                        <div style="font-size: 16px; font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($admin['staff_tel']); ?></div>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-light);">
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--bg-primary); display: flex; align-items: center; justify-content: center; color: var(--primary-color); margin-right: 16px;">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Email Address</div>
                                        <div style="font-size: 16px; font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($admin['staff_email']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Information Section -->
                            <div>
                                <h4 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-shield-alt"></i>
                                    Security Information
                                </h4>
                                
                                <div style="display: flex; align-items: center; padding: 16px 0;">
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--bg-primary); display: flex; align-items: center; justify-content: center; color: var(--primary-color); margin-right: 16px;">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Password</div>
                                        <div style="font-size: 16px; font-weight: 500; color: var(--text-primary);">••••••••••</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div style="display: flex; gap: 12px; padding-top: 24px; border-top: 1px solid var(--border-light);">
                                <form action="admin_edit_profile.php" method="post" style="margin: 0;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-edit"></i>
                                        Edit Profile
                                    </button>
                                </form>
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
