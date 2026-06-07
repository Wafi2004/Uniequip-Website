<?php
session_start();

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User";

if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include("db.php");

// Check if club_name is provided
if (!isset($_GET['club_name'])) {
    header("Location: view_club.php?error=no_club_specified");
    exit;
}

$club_name = mysqli_real_escape_string($connect, $_GET['club_name']);

// Get club details for confirmation
$query = "SELECT * FROM club WHERE club_name = '$club_name'";
$result = mysqli_query($connect, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: view_club.php?error=club_not_found");
    exit;
}

$club = mysqli_fetch_assoc($result);

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    $delete = "DELETE FROM club WHERE club_name = '$club_name'";
    if (mysqli_query($connect, $delete)) {
        header("Location: view_club.php?success=club_deleted");
        exit;
    } else {
        $error_message = "Failed to delete club: " . mysqli_error($connect);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Club - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .danger-card {
            border: 2px solid #ef4444;
            background: linear-gradient(135deg, rgb(239 68 68 / 0.05), rgb(220 38 38 / 0.05));
        }
        
        .danger-icon {
            color: #ef4444;
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        .club-details {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin: 20px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-light);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .detail-value {
            color: var(--text-primary);
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
                        <form action="admin_view_record.php" method="post" style="margin: 0;">
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
                            <button type="submit" class="nav-link active">
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
                        <span class="breadcrumb-item">Clubs</span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Delete Club</span>
                    </div>
                    <h1 class="page-title">Delete Club</h1>
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
                <!-- Delete Confirmation -->
                <div class="table-container danger-card">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-exclamation-triangle" style="margin-right: 8px; color: #ef4444;"></i>
                            Confirm Club Deletion
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-warning"></i>
                            This action cannot be undone
                        </div>
                    </div>

                    <div style="padding: 32px; text-align: center;">
                        <div class="danger-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        
                        <h3 style="font-size: 24px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px;">
                            Are you sure you want to delete this club?
                        </h3>
                        
                        <p style="font-size: 16px; color: var(--text-secondary); margin-bottom: 32px; line-height: 1.6;">
                            You are about to permanently delete the club "<strong><?= htmlspecialchars($club['club_name']) ?></strong>". 
                            This action will remove all club data and cannot be undone.
                        </p>

                        <!-- Club Details -->
                        <div class="club-details" style="text-align: left; max-width: 500px; margin: 0 auto;">
                            <h4 style="margin-bottom: 16px; color: var(--text-primary); font-size: 16px;">
                                <i class="fas fa-info-circle" style="margin-right: 8px; color: var(--primary-color);"></i>
                                Club Information
                            </h4>
                            
                            <div class="detail-row">
                                <span class="detail-label">Club Name:</span>
                                <span class="detail-value"><?= htmlspecialchars($club['club_name']) ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Type:</span>
                                <span class="detail-value">
                                    <span class="status-badge <?= $club['type'] === 'Open' ? 'status-available' : 'status-maintenance' ?>">
                                        <?= htmlspecialchars($club['type']) ?>
                                    </span>
                                </span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Advisor:</span>
                                <span class="detail-value"><?= htmlspecialchars($club['adv_name']) ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Contact:</span>
                                <span class="detail-value"><?= htmlspecialchars($club['adv_tel']) ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value"><?= htmlspecialchars($club['adv_email']) ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Staff Number:</span>
                                <span class="detail-value"><?= htmlspecialchars($club['adv_num']) ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value">
                                    <span class="status-badge <?= strtolower($club['status']) === 'active' ? 'status-available' : 'status-unavailable' ?>">
                                        <?= htmlspecialchars($club['status']) ?>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <?php if (isset($error_message)): ?>
                            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin: 24px 0; color: #991b1b;">
                                <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
                                <?= htmlspecialchars($error_message) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 16px; justify-content: center; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border-light);">
                            <a href="view_club.php" class="btn btn-secondary" style="min-width: 140px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                            
                            <form method="post" style="margin: 0;">
                                <button type="submit" name="confirm_delete" class="btn" style="min-width: 140px; background-color: #ef4444; color: white; border: none;">
                                    <i class="fas fa-trash"></i>
                                    Delete Club
                                </button>
                            </form>
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
