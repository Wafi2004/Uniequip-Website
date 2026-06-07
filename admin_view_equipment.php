<?php
session_start();

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User"; // Default fallback

// Admin access only
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include("db.php");

// Fetch filter value if set
$filter = '';
if (isset($_POST['filter'])) {
    $filter = mysqli_real_escape_string($connect, $_POST['filter']);
    if ($filter !== '') {
        $query = "SELECT * FROM equipment WHERE category = '$filter' ORDER BY id_equipment ASC";
    } else {
        $query = "SELECT * FROM equipment ORDER BY id_equipment ASC";
    }
} else {
    $query = "SELECT * FROM equipment ORDER BY id_equipment ASC";
}

$result = mysqli_query($connect, $query);

// Function to calculate actual available quantity
function getActualAvailableQty($connect, $equipment_id, $total_qty) {
    $borrowed_query = "SELECT COALESCE(SUM(be.qty), 0) as borrowed_qty 
                      FROM booking_equipment be
                      INNER JOIN booking b ON be.id_booking = b.id_booking
                      WHERE be.id_equipment = '$equipment_id' 
                      AND b.status = 'Borrowed'";
    
    $borrowed_result = mysqli_query($connect, $borrowed_query);
    
    if ($borrowed_result) {
        $borrowed_row = mysqli_fetch_assoc($borrowed_result);
        $borrowed_qty = $borrowed_row['borrowed_qty'] ?? 0;
    } else {
        $borrowed_qty = 0;
    }
    
    return $total_qty - $borrowed_qty;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Management - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Delete Confirmation Modal */
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
        .logout-modal {
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

        .logout-modal-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 10px 10px -5px rgb(0 0 0 / 0.04);
            max-width: 400px;
            width: 90%;
            text-align: center;
            animation: modalSlideIn 0.3s ease-out;
        }

        .logout-modal-icon {
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

        .logout-modal h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .logout-modal p {
            font-size: 16px;
            color: var(--text-secondary);
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .logout-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .logout-modal-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 100px;
        }

        .logout-modal-btn-cancel {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .logout-modal-btn-cancel:hover {
            background-color: var(--bg-primary);
        }

        .logout-modal-btn-confirm {
            background-color: #ef4444;
            color: white;
        }

        .logout-modal-btn-confirm:hover {
            background-color: #dc2626;
        }
    </style>
    <script>
        function openImageWindow(imagePath) {
            window.open(imagePath, "ImageWindow", "width=600,height=600,scrollbars=yes,resizable=yes");
        }

        function confirmDelete(equipmentId, equipmentName) {
            document.getElementById('deleteEquipmentName').textContent = equipmentName;
            document.getElementById('deleteEquipmentId').value = equipmentId;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function executeDelete() {
            const equipmentId = document.getElementById('deleteEquipmentId').value;
            window.location.href = 'delete_equipment.php?id_equipment=' + equipmentId;
        }

        // Logout functions
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

        // Close modal when clicking outside
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
                        <button class="nav-link active">
                            <div class="nav-icon"><i class="fas fa-tools"></i></div>
                            View Equipment
                        </button>
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
                        <span class="breadcrumb-item active">Equipment</span>
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
                    <form method="post" action="admin_view_equipment.php" style="display: flex; gap: 16px; align-items: end; flex-wrap: wrap; width: 100%;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">
                                <i class="fas fa-filter" style="margin-right: 6px;"></i>
                                Filter by Category
                            </label>
                            <select name="filter" class="form-select" style="min-width: 200px;">
                                <option value="">-- All Categories --</option>
            <?php
            $categories = [
                'Stage Equipment',
                'Audio Equipment',
                'Visual Equipment',
                'Lighting Equipment',
                'Furniture & Seating',
                'Tents & Canopies',
                'Decor & Draping',
                'Power & Electrical',
                'Staging & Structures',
                'Signage & Display',
                'Catering Equipment',
                'Climate Control',
                'Event Technology',
                'Sanitation & Safety',
                'Transportation & Storage'
            ];

            foreach ($categories as $cat) {
                $selected = ($filter === $cat) ? 'selected' : '';
                echo "<option value='$cat' $selected>$cat</option>";
            }
            ?>
        </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height: 35px; min-width: 160px;">
                            <i class="fas fa-search"></i>
                            Apply Filter
                        </button>
                        <a href="add_equipment.php" class="btn btn-primary" style="height: 35px; min-width: 160px;">
                            <i class="fas fa-plus"></i>
                            Add New Equipment
                        </a>
    </form>
                </div>

                <!-- Equipment Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            Equipment Inventory
                            <?php if ($filter): ?>
                                <span style="color: var(--text-secondary); font-weight: 400; font-size: 0.9rem;">
                                    - Filtered by: <?= htmlspecialchars($filter) ?>
                                </span>
                            <?php endif; ?>
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-database"></i>
                            <?= mysqli_num_rows($result) ?> items
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <table class="data-table">
                                <thead>
        <tr>
            <th>ID</th>
                                        <th>Equipment Name</th>
            <th>Category</th>
                                        <th>Total Qty</th>
                                        <th>Available</th>
            <th>Model</th>
                                        <th>Status</th>
            <th>Picture</th>
            <th>Actions</th>
        </tr>
                                </thead>
                                <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { 
                // Calculate actual available quantity
                $actual_available = getActualAvailableQty($connect, $row['id_equipment'], $row['qty']);
            ?>
                <tr>
                                    <td>
                                        <strong style="color: var(--text-primary);">#<?= $row['id_equipment'] ?></strong>
                                    </td>
                                    <td>
                                        <strong style="color: var(--text-primary);"><?= htmlspecialchars($row['name']) ?></strong>
                                    </td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td>
                                        <span style="font-weight: 600; color: var(--text-primary);"><?= $row['qty'] ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: <?= $actual_available > 0 ? 'var(--accent-color)' : 'var(--error-color)' ?>;">
                                            <?= $actual_available ?>
                                        </span>
                                    </td>
                                    <td><?= $row['model'] ? htmlspecialchars($row['model']) : '<em style="opacity: 0.6;">Not specified</em>' ?></td>
                                    <td>
                                        <?php 
                                        // Determine status based on actual availability
                                        if ($actual_available <= 0) {
                                            $status = 'Unavailable';
                                            $statusClass = 'status-unavailable';
                                        } elseif (!empty($row['status']) && stripos($row['status'], 'maintenance') !== false) {
                                            $status = $row['status'];
                                            $statusClass = 'status-maintenance';
                                        } else {
                                            $status = 'Available';
                                            $statusClass = 'status-available';
                                        }
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </td>
                    <td>
                        <?php if (!empty($row['picture']) && file_exists($row['picture'])) { ?>
                                            <button onclick="openImageWindow('<?= $row['picture'] ?>')" class="btn btn-view btn-sm">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </button>
                        <?php } else { ?>
                                            <span style="opacity: 0.5; font-style: italic;">No Image</span>
                        <?php } ?>
                    </td>
                    <td>
                                        <div class="action-buttons">
                                            <a href="update_equipment.php?id_equipment=<?= $row['id_equipment'] ?>" class="btn btn-edit btn-sm">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </a>
                                            <button onclick="confirmDelete(<?= $row['id_equipment'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>')" 
                                                    class="btn btn-delete btn-sm">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </button>
                                        </div>
                    </td>
                </tr>
            <?php } ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h3>No Equipment Found</h3>
                                <p>
                                    <?php if ($filter): ?>
                                        No equipment found in the "<?= htmlspecialchars($filter) ?>" category.
        <?php else: ?>
                                        No equipment has been added to the inventory yet.
                                    <?php endif; ?>
                                </p>
                                <?php if (!$filter): ?>
                                    <div style="margin-top: 20px;">
                                        <a href="add_equipment.php" class="btn btn-primary">
                                            <i class="fas fa-plus"></i>
                                            Add Your First Equipment
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
            <h3>Delete Equipment</h3>
            <p>Are you sure you want to delete "<span id="deleteEquipmentName"></span>"? This action cannot be undone.</p>
            <div class="delete-modal-actions">
                <button class="delete-modal-btn delete-modal-btn-cancel" onclick="closeDeleteModal()">
                    Cancel
                </button>
                <button class="delete-modal-btn delete-modal-btn-confirm" onclick="executeDelete()">
                    Delete
                </button>
            </div>
            <input type="hidden" id="deleteEquipmentId" value="">
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="logout-modal" onclick="if(event.target === this) hideLogoutModal()">
        <div class="logout-modal-content">
            <div class="logout-modal-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h3>Confirm Logout</h3>
            <p>
                Are you sure you want to logout? You will need to login again to access the dashboard.
            </p>
            <div class="logout-modal-actions">
                <button class="logout-modal-btn logout-modal-btn-cancel" onclick="hideLogoutModal()">
                    Cancel
                </button>
                <button class="logout-modal-btn logout-modal-btn-confirm" onclick="proceedLogout()">
                    Yes, Logout
                </button>
            </div>
        </div>
    </div>
</body>
</html>