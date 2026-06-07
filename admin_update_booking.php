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

$message = "";
$message_type = "";
$booking_details = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_booking = $_POST['id_booking'];
    $new_status = $_POST['new_status'];

    // Get booking details for display
    $stmt = $connect->prepare("
        SELECT b.id_booking, b.event_name, b.status, b.start_date, b.end_date, b.club_name, b.stud_num,
               u.stud_name
        FROM booking b
        JOIN user u ON b.stud_num = u.stud_num
        WHERE b.id_booking = ?
    ");
    $stmt->bind_param("i", $id_booking);
    $stmt->execute();
    $booking_result = $stmt->get_result();
    $booking_details = $booking_result->fetch_assoc();
    $stmt->close();

    if ($new_status === "approved") {
        // Fetch equipment in this booking
        $stmt = $connect->prepare("SELECT id_equipment, qty FROM booking_equipment WHERE id_booking = ?");
        $stmt->bind_param("i", $id_booking);
        $stmt->execute();
        $equipment_result = $stmt->get_result();

        $conflict = false;
        $conflict_details = [];

        while ($row = $equipment_result->fetch_assoc()) {
            $id_equipment = $row['id_equipment'];
            $requested_qty = $row['qty'];

            // Get total stock and equipment name
            $stmt_stock = $connect->prepare("SELECT qty, name FROM equipment WHERE id_equipment = ?");
            $stmt_stock->bind_param("i", $id_equipment);
            $stmt_stock->execute();
            $stock_result = $stmt_stock->get_result();
            $stock_data = $stock_result->fetch_assoc();
            $total_qty = $stock_data['qty'];
            $equipment_name = $stock_data['name'];
            $stmt_stock->close();

            // Calculate total booked for overlapping approved/borrowed bookings (excluding this one)
            $stmt_booked = $connect->prepare("
                SELECT SUM(be.qty)
                FROM booking_equipment be
                JOIN booking b ON be.id_booking = b.id_booking
                WHERE be.id_equipment = ?
                  AND b.id_booking != ?
                  AND b.status IN ('approved', 'borrowed')
                  AND NOT (b.end_date < ? OR b.start_date > ?)
            ");
            $stmt_booked->bind_param("iiss", $id_equipment, $id_booking, $booking_details['start_date'], $booking_details['end_date']);
            $stmt_booked->execute();
            $stmt_booked->bind_result($already_booked_qty);
            $stmt_booked->fetch();
            $stmt_booked->close();

            $already_booked_qty = $already_booked_qty ?? 0;
            $available_qty = $total_qty - $already_booked_qty;

            if ($requested_qty > $available_qty) {
                $conflict = true;
                $conflict_details[] = [
                    'name' => $equipment_name,
                    'id' => $id_equipment,
                    'requested' => $requested_qty,
                    'available' => $available_qty,
                    'total' => $total_qty,
                    'booked' => $already_booked_qty
                ];
            }
        }

        if ($conflict) {
            $message_type = "error";
            $message = "Equipment availability conflict detected. Cannot approve booking.";
        } else {
            // No conflict, update status
            $stmt = $connect->prepare("UPDATE booking SET status = ? WHERE id_booking = ?");
            if (!$stmt) {
                die("Query error: " . $connect->error);
            }
            $stmt->bind_param("si", $new_status, $id_booking);
            
            if ($stmt->execute()) {
                $message_type = "success";
                $message = "Booking has been successfully approved!";
                $booking_details['status'] = $new_status; // Update local copy
            } else {
                $message_type = "error";
                $message = "Failed to update booking status. Please try again.";
            }
            $stmt->close();
        }
    } else {
        // Update to other status (rejected, pending, etc.)
        $stmt = $connect->prepare("UPDATE booking SET status = ? WHERE id_booking = ?");
        $stmt->bind_param("si", $new_status, $id_booking);
        
        if ($stmt->execute()) {
            $message_type = "success";
            $message = "Booking status has been updated to " . ucfirst($new_status) . "!";
            $booking_details['status'] = $new_status; // Update local copy
        } else {
            $message_type = "error";
            $message = "Failed to update booking status. Please try again.";
        }
        $stmt->close();
    }
} else {
    header("Location: admin_view_all_record.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Booking Status - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Alert Styles */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-lg);
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background-color: rgb(16 185 129 / 0.1);
            border-color: rgb(16 185 129 / 0.2);
            color: var(--accent-color);
        }

        .alert-error {
            background-color: rgb(239 68 68 / 0.1);
            border-color: rgb(239 68 68 / 0.2);
            color: var(--error-color);
        }

        .alert-icon {
            font-size: 20px;
            margin-top: 2px;
        }

        .alert-content h4 {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .alert-content p {
            margin: 0;
            line-height: 1.5;
        }

        /* Booking Details Card */
        .booking-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .booking-header {
            background: linear-gradient(135deg, var(--primary-color), #4338ca);
            color: white;
            padding: 20px 24px;
        }

        .booking-id {
            font-family: monospace;
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .booking-title {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .booking-body {
            padding: 24px;
        }

        .booking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .booking-field {
            display: flex;
            flex-direction: column;
        }

        .booking-field-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .booking-field-value {
            font-size: 14px;
            color: var(--text-primary);
            font-weight: 500;
        }

        /* Conflict Details */
        .conflict-list {
            background-color: var(--bg-primary);
            border-radius: var(--radius-lg);
            padding: 16px;
            margin-top: 16px;
        }

        .conflict-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .conflict-item:last-child {
            border-bottom: none;
        }

        .conflict-equipment {
            flex: 1;
        }

        .conflict-equipment-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .conflict-equipment-id {
            font-size: 12px;
            color: var(--text-muted);
            font-family: monospace;
        }

        .conflict-numbers {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .conflict-stat {
            text-align: center;
        }

        .conflict-stat-label {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .conflict-stat-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .conflict-stat.requested .conflict-stat-value {
            color: var(--error-color);
        }

        .conflict-stat.available .conflict-stat-value {
            color: var(--warning-color);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        /* Auto redirect message */
        .redirect-info {
            background-color: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid var(--border-color);
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
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
                        <a href="admin_view_all_record.php" class="breadcrumb-item">Booking Records</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Update Booking Status</span>
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
                <!-- Status Message -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <div class="alert-icon">
                            <?php if ($message_type === "success"): ?>
                                <i class="fas fa-check-circle"></i>
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="alert-content">
                            <h4><?php echo $message_type === "success" ? "Success!" : "Error!"; ?></h4>
                            <p><?php echo $message; ?></p>
                        </div>
                    </div>


                <?php endif; ?>

                <!-- Booking Details -->
                <?php if ($booking_details): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <div class="booking-id">Booking ID: #<?php echo htmlspecialchars($booking_details['id_booking']); ?></div>
                            <h2 class="booking-title"><?php echo htmlspecialchars($booking_details['event_name']); ?></h2>
                        </div>
                        <div class="booking-body">
                            <div class="booking-grid">
                                <div class="booking-field">
                                    <div class="booking-field-label">Student Name</div>
                                    <div class="booking-field-value"><?php echo htmlspecialchars($booking_details['stud_name']); ?></div>
                                </div>
                                <div class="booking-field">
                                    <div class="booking-field-label">Student ID</div>
                                    <div class="booking-field-value"><?php echo htmlspecialchars($booking_details['stud_num']); ?></div>
                                </div>
                                <div class="booking-field">
                                    <div class="booking-field-label">Club Name</div>
                                    <div class="booking-field-value"><?php echo htmlspecialchars($booking_details['club_name']); ?></div>
                                </div>
                                <div class="booking-field">
                                    <div class="booking-field-label">Current Status</div>
                                    <div class="booking-field-value">
                                        <span class="status-badge status-<?php echo $booking_details['status']; ?>">
                                            <?php echo ucfirst(htmlspecialchars($booking_details['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="booking-field">
                                    <div class="booking-field-label">Start Date</div>
                                    <div class="booking-field-value"><?php echo date('M d, Y', strtotime($booking_details['start_date'])); ?></div>
                                </div>
                                <div class="booking-field">
                                    <div class="booking-field-label">End Date</div>
                                    <div class="booking-field-value"><?php echo date('M d, Y', strtotime($booking_details['end_date'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Equipment Conflict Details -->
                <?php if ($message_type === "error" && isset($conflict_details) && !empty($conflict_details)): ?>
                    <div class="table-container">
                        <div class="table-header">
                            <h2 class="table-title">
                                <i class="fas fa-exclamation-triangle" style="margin-right: 8px; color: var(--error-color);"></i>
                                Equipment Availability Conflicts
                            </h2>
                        </div>
                        <div class="conflict-list">
                            <?php foreach ($conflict_details as $conflict): ?>
                                <div class="conflict-item">
                                    <div class="conflict-equipment">
                                        <div class="conflict-equipment-name"><?php echo htmlspecialchars($conflict['name']); ?></div>
                                        <div class="conflict-equipment-id">ID: <?php echo $conflict['id']; ?></div>
                                    </div>
                                    <div class="conflict-numbers">
                                        <div class="conflict-stat requested">
                                            <div class="conflict-stat-label">Requested</div>
                                            <div class="conflict-stat-value"><?php echo $conflict['requested']; ?></div>
                                        </div>
                                        <div class="conflict-stat available">
                                            <div class="conflict-stat-label">Available</div>
                                            <div class="conflict-stat-value"><?php echo $conflict['available']; ?></div>
                                        </div>
                                        <div class="conflict-stat">
                                            <div class="conflict-stat-label">Total Stock</div>
                                            <div class="conflict-stat-value"><?php echo $conflict['total']; ?></div>
                                        </div>
                                        <div class="conflict-stat">
                                            <div class="conflict-stat-label">Already Booked</div>
                                            <div class="conflict-stat-value"><?php echo $conflict['booked']; ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <form action="admin_view_all_record.php" method="post" style="margin: 0;">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Booking Records
                        </button>
                    </form>
                    
                    <?php if ($message_type === "error"): ?>
                        <form action="admin_dashboard.php" method="post" style="margin: 0;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt"></i>
                                Go to Dashboard
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 