<?php
session_start();
include("db.php");

// Debugging (optional - remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure admin is logged in
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User";
$staff_num = $_SESSION['user_number'];

// Fetch admin data
$query = "SELECT * FROM admin WHERE staff_num = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("s", $staff_num);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
} else {
    echo "<script>alert('Admin profile not found.'); window.location.href='admin_profile.php';</script>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_profile'])) {
    $new_name = trim($_POST['staff_name'] ?? '');
    $new_password = trim($_POST['staff_password'] ?? '');
    $new_tel = trim($_POST['staff_tel'] ?? '');
    $new_email = trim($_POST['staff_email'] ?? '');
    
    // Basic validation
    if (empty($new_name) || empty($new_password) || empty($new_tel) || empty($new_email)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Update admin data
        $update_query = "UPDATE admin SET staff_name = ?, staff_password = ?, staff_tel = ?, staff_email = ? WHERE staff_num = ?";
        $update_stmt = $connect->prepare($update_query);
        $update_stmt->bind_param("sssss", $new_name, $new_password, $new_tel, $new_email, $staff_num);
        
        if ($update_stmt->execute()) {
            // Update session data
            $_SESSION['staff_name'] = $new_name;
            $success_message = "Profile updated successfully!";
            
            // Refresh admin data
            $admin['staff_name'] = $new_name;
            $admin['staff_password'] = $new_password;
            $admin['staff_tel'] = $new_tel;
            $admin['staff_email'] = $new_email;
        } else {
            $error_message = "Failed to update profile. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - UniEquip</title>
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

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
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
                        <form action="admin_profile.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link active">
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
                        <span class="breadcrumb-item">Profile</span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Edit Profile</span>
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
                <!-- Edit Profile Form -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-user-edit" style="margin-right: 8px; color: var(--primary-color);"></i>
                            Edit Administrator Profile
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-info-circle"></i>
                            Update your account information
                        </div>
                    </div>

                    <div style="padding: 32px;">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" style="max-width: 600px; margin: 0 auto;">
                            <div style="display: grid; gap: 24px;">
                                <!-- Staff Number (Read-only) -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-id-badge" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Staff Number (Read-only)
                                    </label>
                                    <input type="text" name="staff_num" class="form-input" readonly 
                                           value="<?php echo htmlspecialchars($admin['staff_num']); ?>"
                                           style="padding: 12px 16px; font-size: 14px; background-color: var(--bg-primary); cursor: not-allowed;">
                                </div>

                                <!-- Staff Name -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Full Name *
                                    </label>
                                    <input type="text" name="staff_name" class="form-input" required 
                                           value="<?php echo htmlspecialchars($admin['staff_name']); ?>"
                                           placeholder="Enter your full name" 
                                           style="padding: 12px 16px; font-size: 14px;">
                                </div>

                                <!-- Password -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-lock" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Password *
                                    </label>
                                    <input type="password" name="staff_password" class="form-input" required 
                                           value="<?php echo htmlspecialchars($admin['staff_password']); ?>"
                                           placeholder="Enter your password" 
                                           style="padding: 12px 16px; font-size: 14px;">
                                </div>

                                <!-- Telephone -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-phone" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Phone Number *
                                    </label>
                                    <input type="tel" name="staff_tel" class="form-input" required 
                                           value="<?php echo htmlspecialchars($admin['staff_tel']); ?>"
                                           placeholder="Enter your phone number" 
                                           style="padding: 12px 16px; font-size: 14px;">
                                </div>

                                <!-- Email -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-envelope" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Email Address *
                                    </label>
                                    <input type="email" name="staff_email" class="form-input" required 
                                           value="<?php echo htmlspecialchars($admin['staff_email']); ?>"
                                           placeholder="Enter your email address" 
                                           style="padding: 12px 16px; font-size: 14px;">
                                </div>

                                <!-- Action Buttons -->
                                <div style="display: flex; gap: 12px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                                    <button type="submit" name="submit_profile" class="btn btn-primary" style="min-width: 140px;">
                                        <i class="fas fa-save"></i>
                                        Save Changes
                                    </button>
                                    <a href="admin_profile.php" class="btn btn-secondary" style="min-width: 140px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-times"></i>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
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
