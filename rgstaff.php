<?php
session_start();
include("db.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if not admin
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User";

if (isset($_POST["submit"])) {
    $staff_name_form = $_POST['staff_name'];
    $staff_num = $_POST['staff_num'];
    $staff_password = $_POST['staff_password'];
    $staff_tel = $_POST['staff_tel'];
    $staff_email = $_POST['staff_email'];

    // Check for empty fields
    if (empty($staff_name_form) || empty($staff_num) || empty($staff_password) || empty($staff_tel) || empty($staff_email)) {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
        exit;
    }

    // Check if staff number already exists
    $stmt = $connect->prepare("SELECT * FROM admin WHERE staff_num = ?");
    $stmt->bind_param("s", $staff_num);
    $stmt->execute();
    $check = $stmt->get_result();

    if (!$check) {
        echo "<script>alert('Query error: " . $connect->error . "'); window.history.back();</script>";
        exit;
    }

    if ($check->num_rows > 0) {
        echo "<script>alert('Staff number already exists.'); window.history.back();</script>";
        exit;
    }
    $stmt->close();

    // Insert into database
    $insert_stmt = $connect->prepare("INSERT INTO admin (staff_name, staff_num, staff_password, staff_tel, staff_email) 
        VALUES (?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("sssss", $staff_name_form, $staff_num, $staff_password, $staff_tel, $staff_email);
    $insert = $insert_stmt->execute();

    if ($insert) {
        echo "<script>alert('Staff registration successful. Redirecting to dashboard...'); window.location.href = 'admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error inserting data: " . $connect->error . "'); window.history.back();</script>";
    }
    $insert_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Registration - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Registration Form Styles */
        .registration-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .registration-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 32px;
            box-shadow: var(--shadow-md);
        }

        .registration-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .registration-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary-color), #4338ca);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
            font-size: 24px;
        }

        .registration-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .registration-subtitle {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
        }

        .form-input {
            width: 100%;
            padding: 12px 12px 12px 44px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 14px;
            background-color: var(--bg-secondary);
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(79 70 229 / 0.1);
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-submit {
            flex: 1;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
        }

        .btn-reset {
            flex: 1;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 12px 24px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-reset:hover {
            background-color: var(--bg-primary);
            transform: translateY(-1px);
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

        @media (max-width: 768px) {
            .registration-card {
                padding: 24px;
                margin: 16px;
            }

            .form-actions {
                flex-direction: column;
            }
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
                        <form action="admin_view_all_record.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link">
                                <div class="nav-icon"><i class="fas fa-list"></i></div>
                                All Records
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
                        <button class="nav-link active">
                            <div class="nav-icon"><i class="fas fa-user-plus"></i></div>
                            Register Admin
                        </button>
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
                        <span class="breadcrumb-item">Account</span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Register Admin</span>
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
                <div class="registration-container">
                    <div class="registration-card">
                        <div class="registration-header">
                            <div class="registration-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h2 class="registration-title">Register New Admin</h2>
                            <p class="registration-subtitle">Create a new administrator account for the UniEquip system</p>
                        </div>

                        <form action="" method="post">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="staff_name">Staff Name</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" id="staff_name" name="staff_name" class="form-input" placeholder="Enter staff full name" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="staff_num">Staff Number</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-id-badge input-icon"></i>
                                        <input type="text" id="staff_num" name="staff_num" class="form-input" placeholder="Enter staff ID number" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="staff_password">Password</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" id="staff_password" name="staff_password" class="form-input" placeholder="Enter secure password" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="staff_tel">Phone Number</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-phone input-icon"></i>
                                        <input type="text" id="staff_tel" name="staff_tel" class="form-input" placeholder="Enter phone number" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="staff_email">Email Address</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-envelope input-icon"></i>
                                        <input type="email" id="staff_email" name="staff_email" class="form-input" placeholder="Enter email address" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="submit" class="btn-submit">
                                    <i class="fas fa-user-plus"></i>
                                    Register Admin
                                </button>
                                <button type="reset" class="btn-reset">
                                    <i class="fas fa-undo"></i>
                                    Reset Form
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 12px; margin-top: 24px; justify-content: center;">
                        <button onclick="window.location.href='admin_dashboard.php'" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </button>
                    </div>
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