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

if (!isset($_GET['id_booking'])) {
    echo "Booking ID not provided.";
    exit;
}

$id_booking = $_GET['id_booking'];

// Fetch booking details first
$booking_stmt = $connect->prepare("
    SELECT b.id_booking, b.event_name, b.club_name, b.start_date, b.end_date, u.stud_name
    FROM booking b
    JOIN user u ON b.stud_num = u.stud_num
    WHERE b.id_booking = ?
");
$booking_stmt->bind_param("i", $id_booking);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
$booking_info = $booking_result->fetch_assoc();

// Fetch booking equipment with all needed columns
$stmt = $connect->prepare("
    SELECT 
        e.id_equipment,
        e.name AS equipment_name, 
        e.model,
        e.category,
        e.picture,
        be.qty
    FROM booking_equipment be
    JOIN equipment e ON be.id_equipment = e.id_equipment
    WHERE be.id_booking = ?
");
$stmt->bind_param("i", $id_booking);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Booking #<?= htmlspecialchars($id_booking) ?> Equipment - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Equipment Card */
        .equipment-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.2s ease;
        }

        .equipment-card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--warning-color);
        }

        .equipment-header {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .equipment-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--warning-color), #d97706);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .equipment-details h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .equipment-meta {
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .equipment-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .info-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .info-value {
            color: var(--text-primary);
            font-weight: 600;
        }

        .quantity-badge {
            background-color: var(--warning-color);
            color: white;
            padding: 4px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 600;
        }

        .category-badge {
            background-color: rgb(139 92 246 / 0.1);
            color: #8b5cf6;
            padding: 4px 12px;
            border-radius: var(--radius-sm);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .image-section {
            border-top: 1px solid var(--border-light);
            padding-top: 16px;
            text-align: center;
        }

        .btn-view-image {
            background-color: rgb(59 130 246 / 0.1);
            color: #3b82f6;
            border: 1px solid rgb(59 130 246 / 0.2);
            padding: 10px 20px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-view-image:hover {
            background-color: rgb(59 130 246 / 0.2);
            border-color: rgb(59 130 246 / 0.3);
            color: #2563eb;
            transform: translateY(-1px);
        }

        .no-image {
            color: var(--text-muted);
            font-style: italic;
            font-size: 14px;
        }

        /* Booking Info Card */
        .booking-info-card {
            background: linear-gradient(135deg, var(--warning-color), #d97706);
            border-radius: var(--radius-xl);
            padding: 24px;
            color: white;
            margin-bottom: 24px;
        }

        .booking-info-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .booking-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .booking-info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .booking-info-label {
            font-size: 12px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .booking-info-value {
            font-size: 16px;
            font-weight: 600;
        }

        /* Return Status */
        .return-status {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-md);
            padding: 12px;
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
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

        function openImageWindow(imagePath) {
            window.open(imagePath, "ImageWindow", "width=800,height=800,scrollbars=yes,resizable=yes");
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
                        <a href="return_equipment.php" class="breadcrumb-item">Return Equipment</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Booking #<?= htmlspecialchars($id_booking) ?></span>
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
                <?php if ($booking_info): ?>
                    <!-- Booking Information Card -->
                    <div class="booking-info-card">
                        <div class="booking-info-header">
                            <i class="fas fa-undo" style="font-size: 24px;"></i>
                            <div>
                                <h2 style="margin: 0; font-size: 20px;">Return Booking #<?= htmlspecialchars($id_booking) ?></h2>
                                <p style="margin: 0; opacity: 0.9; font-size: 14px;">Equipment details for return verification</p>
                            </div>
                        </div>
                        <div class="booking-info-grid">
                            <div class="booking-info-item">
                                <div class="booking-info-label">Event Name</div>
                                <div class="booking-info-value"><?= htmlspecialchars($booking_info['event_name']) ?></div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-label">Club</div>
                                <div class="booking-info-value"><?= htmlspecialchars($booking_info['club_name']) ?></div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-label">Borrowed By</div>
                                <div class="booking-info-value"><?= htmlspecialchars($booking_info['stud_name']) ?></div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-label">Duration</div>
                                <div class="booking-info-value">
                                    <?= date('M d, Y', strtotime($booking_info['start_date'])) ?> - <?= date('M d, Y', strtotime($booking_info['end_date'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="return-status">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Status:</strong> Currently Borrowed - Ready for Return Processing
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Equipment List -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-tools" style="margin-right: 8px; color: var(--warning-color);"></i>
                            Borrowed Equipment List
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-database"></i>
                            <?php echo $result->num_rows; ?> item<?php echo $result->num_rows !== 1 ? 's' : ''; ?> to be returned
                        </div>
                    </div>

                    <?php if ($result->num_rows === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-tools"></i>
                            <h3>No Equipment Found</h3>
                            <p>No equipment items were found for this booking.</p>
                        </div>
                    <?php else: ?>
                        <div style="padding: 24px;">
                <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="equipment-card">
                                    <div class="equipment-header">
                                        <div class="equipment-icon">
                                            <i class="fas fa-undo"></i>
                                        </div>
                                        <div class="equipment-details">
                                            <h3><?= htmlspecialchars($row['equipment_name']) ?></h3>
                                            <div class="equipment-meta">
                                                <span><i class="fas fa-hashtag"></i> ID: <?= htmlspecialchars($row['id_equipment']) ?></span>
                                                <span class="category-badge"><?= htmlspecialchars($row['category']) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="equipment-info">
                                        <div class="info-item">
                                            <span class="info-label">Model:</span>
                                            <span class="info-value"><?= htmlspecialchars($row['model'] ?? 'Not specified') ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Category:</span>
                                            <span class="info-value"><?= htmlspecialchars($row['category']) ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Quantity to Return:</span>
                                            <span class="quantity-badge"><?= $row['qty'] ?> unit<?= $row['qty'] !== 1 ? 's' : '' ?></span>
                                        </div>
                                    </div>

                                    <div class="image-section">
                            <?php 
                            $imageFile = ltrim($row['picture'], 'uploads/');
                            $imagePath = 'uploads/' . $imageFile;
                            
                            if (!empty($row['picture']) && file_exists($imagePath)): ?>
                                            <button onclick="openImageWindow('<?= $imagePath ?>')" class="btn-view-image">
                                                <i class="fas fa-image"></i>
                                                View Equipment Image
                                </button>
                            <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-image" style="margin-right: 8px; opacity: 0.5;"></i>
                                                <?= !empty($row['picture']) ? "Image missing" : "No image available for this equipment" ?>
                                            </div>
                            <?php endif; ?>
                                    </div>
                                </div>
                <?php endwhile; ?>
                        </div>
            <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <a href="return_equipment.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Returns
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i>
                        Print Return List
                    </button>
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