<?php
session_start();
include("db.php");

// Show errors during development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if student is logged in
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'student') {
    echo "<script>alert('Access denied. Please login as student.'); window.location.href='login.php';</script>";
    exit();
}

$stud_num = $_SESSION['user_number'];

// Fetch student data for welcome message
$query = "SELECT stud_name FROM user WHERE stud_num = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("s", $stud_num);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $student = $result->fetch_assoc();
    $student_name = $student['stud_name'];
} else {
    $student_name = "Student";
}

// Get all bookings for this student
$booking_stmt = $connect->prepare("
    SELECT * FROM booking 
    WHERE stud_num = ? 
    ORDER BY start_date DESC
");
$booking_stmt->bind_param("s", $stud_num);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - UniEquip</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- User Common Styles -->
    <link href="user_style.css" rel="stylesheet">
    
    <style>
        /* Modern Navbar and Common Styles - Consistent with other pages */
        .modern-navbar {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            border-bottom: 1px solid rgba(255,255,255,0.3);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .modern-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.4rem;
            text-decoration: none;
            position: relative;
            padding: 8px 16px;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .modern-brand:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .brand-logo {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .logo-img {
            width: 35px;
            height: 35px;
            object-fit: contain;
            transition: all 0.3s ease;
        }

        .modern-brand:hover .brand-logo {
            transform: rotate(-5deg) scale(1.1);
            box-shadow: 0 12px 30px rgba(0,0,0,0.2);
        }

        .brand-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }

        .modern-brand:hover .brand-icon {
            transform: rotate(5deg) scale(1.1);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.5);
        }

        .brand-text {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }

        .brand-badge {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        .modern-toggler {
            border: none;
            padding: 8px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
            width: 45px;
            height: 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 4px;
            transition: all 0.3s ease;
        }

        .modern-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .modern-toggler span {
            display: block;
            width: 20px;
            height: 2px;
            background: #667eea;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .modern-toggler:hover span {
            background: #764ba2;
        }

        .modern-nav {
            gap: 8px;
        }

        .modern-nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px !important;
            border-radius: 12px;
            font-weight: 500;
            color: #4c5f7a !important;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .modern-nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .modern-nav-link:hover::before {
            left: 100%;
        }

        .modern-nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea !important;
            transform: translateY(-2px);
        }

        .modern-nav-link.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white !important;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .modern-nav-link.active:hover {
            background: linear-gradient(135deg, #764ba2, #f093fb);
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.6);
        }

        .nav-icon {
            width: 35px;
            height: 35px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .modern-nav-link.active .nav-icon {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .modern-nav-link:hover .nav-icon {
            background: rgba(102, 126, 234, 0.2);
            transform: scale(1.1);
        }

        .logout-link:hover {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
        }

        .logout-link:hover .nav-icon {
            background: rgba(239, 68, 68, 0.2) !important;
            color: #ef4444;
        }

        /* Dropdown Menu Styles */
        .dropdown-menu {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            padding: 0.75rem 0;
            margin-top: 0.5rem;
            min-width: 200px;
        }

        .dropdown-item {
            padding: 12px 20px;
            color: #4c5f7a;
            font-weight: 500;
            border-radius: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .dropdown-item:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            transform: translateX(5px);
        }

        .dropdown-item.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .dropdown-item.active:hover {
            background: linear-gradient(135deg, #764ba2, #f093fb);
            color: white;
            transform: translateX(5px);
        }

        .dropdown-toggle::after {
            margin-left: 8px;
            vertical-align: middle;
        }

        /* Booking Specific Styles */
        .booking-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .booking-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 4s infinite;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .booking-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .booking-id {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .booking-status {
            padding: 6px 15px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }

        .status-approved {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .status-rejected {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 1rem;
        }

        .detail-content h6 {
            margin: 0;
            color: #4c5f7a;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-content p {
            margin: 0;
            color: #2d3748;
            font-weight: 500;
            font-size: 1rem;
        }

        .equipment-table {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: relative;
            z-index: 1;
        }

        .table {
            margin: 0;
        }

        .table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            border: none;
            padding: 1rem;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .equipment-image-btn {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
            border: none;
            padding: 8px 16px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(78, 205, 196, 0.3);
        }

        .equipment-image-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(78, 205, 196, 0.5);
            color: white;
        }

        .no-image {
            color: #94a3b8;
            font-style: italic;
            font-size: 0.9rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: #667eea;
            animation: float 3s ease-in-out infinite;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .modern-navbar {
                padding: 0.75rem 0;
            }
            
            .modern-nav {
                margin-top: 1rem;
                gap: 4px;
            }
            
            .modern-nav-link {
                padding: 10px 15px !important;
                margin: 2px 0;
            }
            
            .brand-badge {
                display: none;
            }
            
            .booking-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .booking-details {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .equipment-table {
                font-size: 0.9rem;
            }
            
            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }
        }

        /* Admin Dashboard Style Modal */
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
            color: #374151;
            margin-bottom: 12px;
        }

        .modal-message {
            font-size: 16px;
            color: #6b7280;
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
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .modal-btn-cancel:hover {
            background-color: #e5e7eb;
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
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top modern-navbar">
        <div class="container">
            <a class="navbar-brand modern-brand" href="#">
                <div class="brand-logo">
                    <img src="uitm.png" alt="UiTM Logo" class="logo-img">
                </div>
                <span class="brand-text">UniEquip</span>
            </a>
            <button class="navbar-toggler modern-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto modern-nav">
                    <li class="nav-item">
                        <a class="nav-link modern-nav-link" href="user_dashboard.php">
                            <div class="nav-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link modern-nav-link" href="user_profile.php">
                            <div class="nav-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link modern-nav-link dropdown-toggle active" href="#" id="bookingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="nav-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <span>Booking</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="bookingDropdown">
                            <li><a class="dropdown-item" href="addBooking.php">
                                <i class="fas fa-plus-circle me-2"></i>New Booking
                            </a></li>
                            <li><a class="dropdown-item active" href="user_view_booking.php">
                                <i class="fas fa-list me-2"></i>View Booking
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link modern-nav-link logout-link" href="#" onclick="showLogoutModal(); return false;">
                            <div class="nav-icon">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container" style="margin-top: 100px;">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="main-container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="user-avatar">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h2 class="page-title with-sparkle mb-2">My Bookings</h2>
                        <p class="text-muted">Welcome, <?php echo htmlspecialchars($student_name); ?>! Here are all your equipment bookings and their current status.</p>
                    </div>

                    <!-- Bookings Content -->
                    <?php if ($booking_result->num_rows === 0): ?>
                        <div class="content-card">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                                <h4 class="mb-3">No Bookings Found</h4>
                                <p class="mb-4">You haven't made any equipment bookings yet. Start by creating your first booking!</p>
                                <a href="addBooking.php" class="btn btn-custom">
                                    <i class="fas fa-plus me-2"></i>Create New Booking
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php while ($booking = $booking_result->fetch_assoc()): ?>
                            <div class="booking-card">
                                <!-- Booking Header -->
                                <div class="booking-header">
                                    <div class="booking-id">
                                        <i class="fas fa-hashtag me-2"></i>Booking ID: <?php echo $booking['id_booking']; ?>
                                    </div>
                                    <div class="booking-status status-<?php echo strtolower($booking['status']); ?>">
                                        <i class="fas fa-circle me-1"></i><?php echo ucfirst($booking['status']); ?>
                                    </div>
                                </div>

                                <!-- Booking Details -->
                                <div class="booking-details">
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="detail-content">
                                            <h6>Event Name</h6>
                                            <p><?php echo htmlspecialchars($booking['event_name']); ?></p>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-calendar-plus"></i>
                                        </div>
                                        <div class="detail-content">
                                            <h6>Start Date</h6>
                                            <p><?php echo date('d M Y', strtotime($booking['start_date'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-calendar-minus"></i>
                                        </div>
                                        <div class="detail-content">
                                            <h6>End Date</h6>
                                            <p><?php echo date('d M Y', strtotime($booking['end_date'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="detail-content">
                                            <h6>Club Name</h6>
                                            <p><?php echo htmlspecialchars($booking['club_name']); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Equipment Table -->
                                <div class="equipment-table">
                                    <h5 class="mb-3 p-3" style="color: #4c5f7a; font-weight: 600;">
                                        <i class="fas fa-box me-2"></i>Booked Equipment
                                    </h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Equipment ID</th>
                                                <th>Equipment Name</th>
                                                <th>Category</th>
                                                <th>Model</th>
                                                <th>Quantity</th>
                                                <th>Picture</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $equip_stmt = $connect->prepare("
                                                SELECT e.id_equipment, e.name, e.category, e.model, e.picture, be.qty 
                                                FROM booking_equipment be
                                                JOIN equipment e ON be.id_equipment = e.id_equipment
                                                WHERE be.id_booking = ?
                                            ");
                                            $equip_stmt->bind_param("i", $booking['id_booking']);
                                            $equip_stmt->execute();
                                            $equip_result = $equip_stmt->get_result();

                                            if ($equip_result->num_rows === 0): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        <i class="fas fa-exclamation-circle me-2"></i>
                                                        No equipment booked for this event.
                                                    </td>
                                                </tr>
                                            <?php else:
                                                $count = 1;
                                                while ($equip = $equip_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><strong><?php echo $count++; ?></strong></td>
                                                        <td><?php echo $equip['id_equipment']; ?></td>
                                                        <td><strong><?php echo htmlspecialchars($equip['name']); ?></strong></td>
                                                        <td><?php echo htmlspecialchars($equip['category']); ?></td>
                                                        <td><?php echo htmlspecialchars($equip['model'] ?? ''); ?></td>
                                                        <td><span class="badge bg-primary"><?php echo $equip['qty']; ?></span></td>
                                                        <td>
                                                            <?php if (!empty($equip['picture'])): ?>
                                                                <form target="_blank" action="<?= htmlspecialchars($equip['picture']) ?>" method="get" style="display:inline;">
                                                                    <button type="submit" class="equipment-image-btn">
                                                                        <i class="fas fa-eye me-1"></i>View
                                                                    </button>
                                                                </form>
                                                            <?php else: ?>
                                                                <span class="no-image">
                                                                    <i class="fas fa-image me-1"></i>No Image
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile;
                                            endif;
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <!-- Back Button -->
                    <div class="text-center mt-4">
                        <a href="user_dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 mb-4 text-center">
                    <h5>
                        <i class="fas fa-tools me-2"></i>UniEquip
                    </h5>
                    <p class="text-muted">Your trusted partner for university equipment management. Streamlining access to academic resources for students and faculty.</p>
                    <div class="social-icons d-flex justify-content-center">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 text-center">
                    <h5>Contact Info</h5>
                    <div class="text-muted">
                        <div class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Universiti Teknologi MARA
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +603-5544 2000
                        </div>
                        <div>
                            <i class="fas fa-envelope me-2"></i>
                            info@uniequip.edu.my
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-1 text-muted">&copy; 2024 UniEquip. All rights reserved.</p>
                    <p class="mb-0 text-muted">Made with <i class="fas fa-heart text-danger"></i> for UiTM students</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal-overlay" onclick="if(event.target === this) hideLogoutModal()">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h2 class="modal-title">Confirm Logout</h2>
            <p class="modal-message">
                Are you sure you want to logout? You will need to login again to access your bookings.
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Logout Modal JavaScript -->
    <script>
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
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLogoutModal();
            }
        });
    </script>
</body>
</html>

<?php
$booking_stmt->close();
$connect->close();
?>
