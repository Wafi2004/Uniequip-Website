<?php
session_start();

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User";

if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include("db.php");

$typeFilter = '';
if (isset($_POST['filter'])) {
    $typeFilter = mysqli_real_escape_string($connect, $_POST['filter']);
    if ($typeFilter !== '') {
        $query = "SELECT * FROM club WHERE type = '$typeFilter' ORDER BY club_name ASC";
    } else {
        $query = "SELECT * FROM club ORDER BY club_name ASC";
    }
} else {
    $query = "SELECT * FROM club ORDER BY club_name ASC";
}
$result = mysqli_query($connect, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Management - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .delete-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
        }

        .delete-modal-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 10px 10px -5px rgb(0 0 0 / 0.04);
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

        .delete-modal-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .delete-modal h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .delete-modal p {
            font-size: 16px;
            color: var(--text-secondary);
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .delete-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .delete-modal-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 100px;
        }

        .delete-modal-btn-cancel {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .delete-modal-btn-cancel:hover {
            background-color: var(--bg-primary);
        }

        .delete-modal-btn-confirm {
            background-color: #ef4444;
            color: white;
        }

        .delete-modal-btn-confirm:hover {
            background-color: #dc2626;
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
        function confirmDelete(clubName) {
            document.getElementById('deleteClubName').textContent = clubName;
            document.getElementById('deleteClubNameInput').value = clubName;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function executeDelete() {
            const clubName = document.getElementById('deleteClubNameInput').value;
            window.location.href = 'delete_club.php?club_name=' + encodeURIComponent(clubName);
        }

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
            const deleteModal = document.getElementById('deleteModal');
            const logoutModal = document.getElementById('logoutModal');
            
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
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
                        <button class="nav-link active">
                            <div class="nav-icon"><i class="fas fa-users"></i></div>
                            Club Details
                        </button>
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
                        <span class="breadcrumb-item active">Clubs</span>
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
                    <form method="post" action="view_club.php" style="display: flex; gap: 16px; align-items: end; flex-wrap: wrap; width: 100%;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">
                                <i class="fas fa-filter" style="margin-right: 6px;"></i>
                                Filter by Type
                            </label>
                            <select name="filter" class="form-select" style="min-width: 200px;">
                                <option value="">-- All Types --</option>
                                <option value="Open" <?= $typeFilter == "Open" ? "selected" : "" ?>>Open</option>
                                <option value="Close" <?= $typeFilter == "Close" ? "selected" : "" ?>>Close</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height: 35px; min-width: 160px;">
                            <i class="fas fa-search"></i>
                            Apply Filter
                        </button>
                        <a href="add_club.php" class="btn btn-primary" style="height: 35px; min-width: 160px;">
                            <i class="fas fa-plus"></i>
                            Add New Club
                        </a>
                    </form>
                </div>

                <!-- Clubs Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            Club Directory
                            <?php if ($typeFilter): ?>
                                <span style="color: var(--text-secondary); font-weight: 400; font-size: 0.9rem;">
                                    - Filtered by: <?= htmlspecialchars($typeFilter) ?>
                                </span>
                            <?php endif; ?>
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-users"></i>
                            <?= mysqli_num_rows($result) ?> clubs
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Club Name</th>
                                        <th>Type</th>
                                        <th>Advisor Name</th>
                                        <th>Contact</th>
                                        <th>Email</th>
                                        <th>Staff No</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                        <tr>
                                            <td>
                                                <strong style="color: var(--text-primary);"><?= htmlspecialchars($row['club_name']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="status-badge <?= $row['type'] === 'Open' ? 'status-available' : 'status-maintenance' ?>">
                                                    <?= htmlspecialchars($row['type']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['adv_name']) ?></td>
                                            <td><?= htmlspecialchars($row['adv_tel']) ?></td>
                                            <td><?= htmlspecialchars($row['adv_email']) ?></td>
                                            <td><?= htmlspecialchars($row['adv_num']) ?></td>
                                            <td>
                                                <span class="status-badge <?= strtolower($row['status']) === 'active' ? 'status-available' : 'status-unavailable' ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="update_club.php?club_name=<?= urlencode($row['club_name']) ?>" class="btn btn-edit btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                        Edit
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <h3>No Clubs Found</h3>
                                <p>
                                    <?php if ($typeFilter): ?>
                                        No clubs found with type "<?= htmlspecialchars($typeFilter) ?>".
                                    <?php else: ?>
                                        No clubs have been registered yet.
                                    <?php endif; ?>
                                </p>
                                <?php if (!$typeFilter): ?>
                                    <div style="margin-top: 20px;">
                                        <a href="add_club.php" class="btn btn-primary">
                                            <i class="fas fa-plus"></i>
                                            Add Your First Club
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="delete-modal" onclick="if(event.target === this) closeDeleteModal()">
        <div class="delete-modal-content">
            <div class="delete-modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Delete Club</h3>
            <p>Are you sure you want to delete "<span id="deleteClubName"></span>"? This action cannot be undone.</p>
            <div class="delete-modal-actions">
                <button class="delete-modal-btn delete-modal-btn-cancel" onclick="closeDeleteModal()">
                    Cancel
                </button>
                <button class="delete-modal-btn delete-modal-btn-confirm" onclick="executeDelete()">
                    Delete
                </button>
            </div>
            <input type="hidden" id="deleteClubNameInput" value="">
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal-overlay" onclick="if(event.target === this) hideLogoutModal()">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-power-off"></i>
            </div>
            <h3 class="modal-title">Logout</h3>
            <p class="modal-message">Are you sure you want to logout?</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" onclick="hideLogoutModal()">
                    Cancel
                </button>
                <button class="modal-btn modal-btn-confirm" onclick="proceedLogout()">
                    Logout
                </button>
            </div>
        </div>
    </div>
</body>
</html>
