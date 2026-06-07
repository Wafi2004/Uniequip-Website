<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "db.php";
session_start();

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

$eventName = $_POST['eventName'];
$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];
$clubName = $_POST['clubName'];

// Fetch equipment excluding Maintenance
$equipment_query = "SELECT * FROM equipment WHERE status != 'Maintenance'";
$equipment_result = mysqli_query($connect,$equipment_query);

// Fetch already booked equipment within the date range and with Approved or Borrowed status
$booked_query = "
    SELECT be.id_equipment, SUM(be.qty) AS booked_qty
    FROM booking_equipment be
    JOIN booking b ON be.id_booking = b.id_booking
    WHERE b.status IN ('approved', 'borrowed')
    AND NOT (b.end_date < '$startDate' OR b.start_date > '$endDate')
    GROUP BY be.id_equipment
";
$booked_result = mysqli_query($connect,$booked_query);

$booked_qtys = [];

foreach ($booked_result as $row) {
    $booked_qtys[$row['id_equipment']] = (int)$row['booked_qty']; 
}

$equipment_rows = [];
$categories = [];

foreach ($equipment_result as $equip) {
    $qty = (int)$equip['qty']; // use qty from DB
    $booked = $booked_qtys[$equip['id_equipment']] ?? 0;
    $available_now = max(0, $qty - $booked); // calculate available

    $equip['booked'] = $booked;
    $equip['available_now'] = $available_now;
    $equip['qty'] = $qty;

    $equipment_rows[] = $equip;
    $categories[$equip['category']] = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Equipment - UniEquip</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- User Common Styles -->
    <link href="user_style.css" rel="stylesheet">
    
    <style>
        /* Modern Navbar Styles - Same as addBooking.php */
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

        /* Equipment Selection Specific Styles */
        .filter-section {
            background: linear-gradient(135deg, #f8faff, #f0f4ff);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(102, 126, 234, 0.1);
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        }

        .filter-label {
            font-weight: 600;
            color: #4c5f7a;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .equipment-table {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
        }

        .equipment-table thead th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1.25rem 1rem;
            font-size: 0.95rem;
            position: relative;
        }

        .equipment-table thead th:first-child {
            border-top-left-radius: 20px;
        }

        .equipment-table thead th:last-child {
            border-top-right-radius: 20px;
        }

        .sort-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            margin-left: 5px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .sort-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .equipment-table tbody td {
            border: none;
            padding: 1rem;
            border-bottom: 1px solid rgba(102, 126, 234, 0.08);
            vertical-align: middle;
        }

        .equipment-table tbody tr {
            transition: all 0.3s ease;
        }

        .equipment-table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .equipment-table tbody tr:last-child td {
            border-bottom: none;
        }

        .equipment-id {
            font-weight: 600;
            color: #667eea;
            font-size: 0.9rem;
        }

        .equipment-name {
            font-weight: 600;
            color: #2d3748;
        }

        .equipment-category {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }

        .qty-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .available-badge {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .view-btn {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.5);
        }

        .book-btn {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.5);
        }

        .na-text {
            color: #9ca3af;
            font-style: italic;
            font-weight: 500;
        }

        .selected-items-section {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            border: 1px solid rgba(34, 197, 94, 0.2);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.1);
        }

        .selected-items-title {
            color: #166534;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .selected-item {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .selected-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .selected-item:last-child {
            margin-bottom: 0;
        }

        .item-info {
            font-weight: 500;
            color: #2d3748;
        }

        .remove-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .remove-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.5);
        }

        .form-select {
            border: 2px solid rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            font-size: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
            background: linear-gradient(135deg, #764ba2, #f093fb);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #64748b, #475569);
            border: none;
            padding: 12px 30px;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(100, 116, 139, 0.3);
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(100, 116, 139, 0.5);
            color: white;
            text-decoration: none;
        }

        /* Booking Info Card */
        .booking-info-card {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(245, 158, 11, 0.2);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.1);
        }

        .booking-info-title {
            color: #92400e;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .booking-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.5rem;
            color: #78350f;
            font-weight: 500;
        }

        .booking-detail:last-child {
            margin-bottom: 0;
        }

        .booking-detail i {
            width: 20px;
            color: #d97706;
        }

        /* Modal Styles - Same as addBooking.php */
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

        /* Responsive Design */
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
        }

        @media (max-width: 768px) {
            .equipment-table {
                font-size: 0.9rem;
            }
            
            .equipment-table thead th,
            .equipment-table tbody td {
                padding: 0.75rem 0.5rem;
            }
            
            .selected-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .booking-info-card {
                padding: 1rem;
            }
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
                    <li class="nav-item">
                        <a class="nav-link modern-nav-link active" href="addBooking.php">
                            <div class="nav-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <span>Booking</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link modern-nav-link" href="available_equipment.php">
                            <div class="nav-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <span>Equipment</span>
                        </a>
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
            <div class="col-lg-12">
                <div class="main-container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="user-avatar">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h2 class="page-title with-sparkle mb-2">Equipment Inventory</h2>
                        <p class="text-muted">Welcome, <?php echo htmlspecialchars($student_name); ?>! Select equipment for your booking.</p>
                    </div>



                    <!-- Filter Section -->
                    <div class="filter-section">
                        <label for="categoryFilter" class="filter-label">
                            <i class="fas fa-filter"></i>
                            Filter by Category
                        </label>
                        <select id="categoryFilter" class="form-select" onchange="filterEquipmentTable()">
                            <option value="all">All Categories</option>
                            <?php foreach (array_keys($categories) as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Equipment Selection Form -->
                    <form method="post" action="confirmBooking.php" onsubmit="return submitFinalList()">
                        <input type="hidden" name="eventName" value="<?= htmlspecialchars($eventName) ?>">
                        <input type="hidden" name="startDate" value="<?= htmlspecialchars($startDate) ?>">
                        <input type="hidden" name="endDate" value="<?= htmlspecialchars($endDate) ?>">
                        <input type="hidden" name="clubName" value="<?= htmlspecialchars($clubName) ?>">
                        <input type="hidden" id="finalList" name="finalList" value="">

                        <!-- Equipment Table -->
                        <div class="table-responsive">
                            <table class="table equipment-table" id="equipmentTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>
                                            Equipment Name
                                            <button type="button" class="sort-btn" onclick="sortTable(1, 'text')">↑</button>
                                            <button type="button" class="sort-btn" onclick="sortTable(1, 'text', true)">↓</button>
                                        </th>
                                        <th>
                                            Category
                                            <button type="button" class="sort-btn" onclick="sortTable(2, 'text')">↑</button>
                                            <button type="button" class="sort-btn" onclick="sortTable(2, 'text', true)">↓</button>
                                        </th>
                                        <th>
                                            Model
                                            <button type="button" class="sort-btn" onclick="sortTable(3, 'text')">↑</button>
                                            <button type="button" class="sort-btn" onclick="sortTable(3, 'text', true)">↓</button>
                                        </th>
                                        <th>Total Qty</th>
                                        <th>Available Qty</th>
                                        <th>Picture</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipment_rows as $equip): ?>
                                        <tr data-category="<?= htmlspecialchars($equip['category']) ?>">
                                            <td><span class="equipment-id"><?= $equip['id_equipment'] ?></span></td>
                                            <td><span class="equipment-name"><?= htmlspecialchars($equip['name']) ?></span></td>
                                            <td><span class="equipment-category"><?= htmlspecialchars($equip['category']) ?></span></td>
                                            <td><?= $equip['model'] !== null ? htmlspecialchars($equip['model']) : '<em class="na-text">No model</em>' ?></td>
                                            <td><span class="qty-badge"><?= $equip['qty'] ?></span></td>
                                            <td><span class="available-badge"><?= $equip['available_now'] ?></span></td>
                                            <td>
                                                <?php if (!empty($equip['picture']) && file_exists($equip['picture'])): ?>
                                                    <button type="button" class="view-btn" onclick="window.open('<?= htmlspecialchars($equip['picture']) ?>', 'PictureWindow', 'width=600,height=600')">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </button>
                                                <?php else: ?>
                                                    <em class="na-text">No Image</em>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($equip['available_now'] > 0): ?>
                                                    <button type="button" class="book-btn" onclick="bookItem(<?= $equip['id_equipment'] ?>, '<?= htmlspecialchars($equip['name']) ?>', <?= $equip['available_now'] ?>)">
                                                        <i class="fas fa-plus me-1"></i>Book
                                                    </button>
                                                <?php else: ?>
                                                    <span class="na-text">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Selected Items Section -->
                        <div class="selected-items-section">
                            <h4 class="selected-items-title">
                                <i class="fas fa-shopping-cart"></i>
                                Selected Equipment
                            </h4>
                            <div id="itemList">
                                <p class="text-muted text-center">No equipment selected yet. Choose from the table above.</p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="text-center mt-4">
                            <a href="addBooking.php" class="btn btn-secondary me-3">
                                <i class="fas fa-arrow-left me-2"></i>Back to Booking Form
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-2"></i>Confirm Booking
                            </button>
                        </div>
                    </form>
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
                Are you sure you want to logout? You will lose any unsaved booking information.
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

    <script>
        let selectedItems = [];

        function filterEquipmentTable() {
            const selectedCategory = document.getElementById('categoryFilter').value;
            const rows = document.querySelectorAll('#equipmentTable tbody tr');
            rows.forEach(row => {
                const rowCat = row.getAttribute('data-category');
                row.style.display = (selectedCategory === 'all' || rowCat === selectedCategory) ? '' : 'none';
            });
        }

        function bookItem(id, name, available) {
            if (selectedItems.some(item => item.id === id)) {
                alert("Item already selected.");
                return;
            }

            let qty = prompt(`Enter quantity for ${name} (Max ${available}):`);
            qty = parseInt(qty);

            if (isNaN(qty) || qty < 1 || qty > available) {
                alert("Invalid quantity. Must be between 1 and " + available);
                return;
            }

            selectedItems.push({ id, name, qty });
            updateSelectedItemsList();
        }

        function updateSelectedItemsList() {
            const itemList = document.getElementById('itemList');
            
            if (selectedItems.length === 0) {
                itemList.innerHTML = '<p class="text-muted text-center">No equipment selected yet. Choose from the table above.</p>';
                return;
            }

            itemList.innerHTML = '';
            selectedItems.forEach((item, index) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'selected-item';
                itemDiv.innerHTML = `
                    <div class="item-info">
                        <strong>${item.name}</strong> - Quantity: ${item.qty}
                    </div>
                    <button type="button" class="remove-btn" onclick="removeItem(${item.id})">
                        <i class="fas fa-trash me-1"></i>Remove
                    </button>
                `;
                itemList.appendChild(itemDiv);
            });
        }

        function removeItem(id) {
            selectedItems = selectedItems.filter(item => item.id !== id);
            updateSelectedItemsList();
        }

        function submitFinalList() {
            if (selectedItems.length === 0) {
                alert("Please select at least one item.");
                return false;
            }
            document.getElementById('finalList').value = JSON.stringify(selectedItems);
            return true;
        }

        function sortTable(colIndex, type, desc = false) {
            const table = document.getElementById("equipmentTable");
            const rows = Array.from(table.rows).slice(1);
            const tbody = table.tBodies[0];

            rows.sort((a, b) => {
                let aText = a.cells[colIndex].innerText.toLowerCase();
                let bText = b.cells[colIndex].innerText.toLowerCase();

                if (type === 'text') {
                    return desc ? bText.localeCompare(aText) : aText.localeCompare(bText);
                }
                if (type === 'number') {
                    return desc ? parseFloat(bText) - parseFloat(aText) : parseFloat(aText) - parseFloat(bText);
                }
                return 0;
            });

            rows.forEach(row => tbody.appendChild(row));
        }

        // Logout Modal JavaScript
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
