<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "db.php";

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

// Initialize variables
$success = false;
$error_message = "";
$booking_id = 0;
$booking_details = [];

// Validate POST data
if (!isset($_POST['eventName'], $_POST['startDate'], $_POST['endDate'], $_POST['clubName'], $_POST['finalList'])) {
    $error_message = "Missing required fields. Please go back and complete the booking form.";
} else {
    // Get POST values
    $eventName = $_POST['eventName'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $clubName = $_POST['clubName'];
    $studNum = $_SESSION['user_number'] ?? $_POST['stud_num']; // fallback if not set in session
    $finalList = json_decode($_POST['finalList'], true);

    // Store booking details for display
    $booking_details = [
        'eventName' => $eventName,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'clubName' => $clubName,
        'equipment' => $finalList
    ];

    // Validate student number
    if (!$studNum) {
        $error_message = "Error: Student number is missing from session.";
    } else {
        // Insert into booking table
        $bookingStmt = $connect->prepare("INSERT INTO booking (stud_num, event_name, status, start_date, end_date, club_name) VALUES (?, ?, 'pending', ?, ?, ?)");
        $bookingStmt->bind_param("sssss", $studNum, $eventName, $startDate, $endDate, $clubName);

        if ($bookingStmt->execute()) {
            $booking_id = $bookingStmt->insert_id;

            // Prepare equipment insert statement
            $equipStmt = $connect->prepare("INSERT INTO booking_equipment (id_equipment, id_booking, qty) VALUES (?, ?, ?)");

            $equipment_success = true;
            foreach ($finalList as $item) {
                $id_equipment = $item['id'];
                $qty = $item['qty'];

                // Insert into booking_equipment
                $equipStmt->bind_param("iii", $id_equipment, $booking_id, $qty);
                if (!$equipStmt->execute()) {
                    $equipment_success = false;
                    break;
                }
            }

            if ($equipment_success) {
                $success = true;
            } else {
                $error_message = "Error adding equipment to booking: " . $connect->error;
            }

            $equipStmt->close();
        } else {
            $error_message = "Error creating booking: " . $connect->error;
        }

        $bookingStmt->close();
    }
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - UniEquip</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- User Common Styles -->
    <link href="user_style.css" rel="stylesheet">
    
    <style>
        /* Modern Navbar Styles - Same as other pages */
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

        /* Success and Error Styles */
        .success-card {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-radius: 25px;
            padding: 3rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(34, 197, 94, 0.2);
            box-shadow: 0 15px 35px rgba(34, 197, 94, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(34, 197, 94, 0.05), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 3rem;
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.4);
            animation: bounceIn 1s ease-out;
            position: relative;
            z-index: 1;
        }

        @keyframes bounceIn {
            0% { transform: scale(0) rotate(180deg); opacity: 0; }
            50% { transform: scale(1.2) rotate(180deg); }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }

        .success-title {
            color: #166534;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .success-message {
            color: #15803d;
            font-size: 1.2rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .booking-id {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
            border: 2px solid rgba(34, 197, 94, 0.3);
            position: relative;
            z-index: 1;
        }

        .booking-id-label {
            color: #166534;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .booking-id-value {
            color: #059669;
            font-size: 2rem;
            font-weight: 800;
            font-family: 'Courier New', monospace;
        }

        .error-card {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border-radius: 25px;
            padding: 3rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(239, 68, 68, 0.2);
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .error-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(239, 68, 68, 0.05), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        .error-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 3rem;
            box-shadow: 0 20px 40px rgba(239, 68, 68, 0.4);
            animation: shake 0.5s ease-in-out;
            position: relative;
            z-index: 1;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error-title {
            color: #dc2626;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .error-message {
            color: #b91c1c;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        /* Booking Details Card */
        .details-card {
            background: linear-gradient(135deg, #f8faff, #f0f4ff);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(102, 126, 234, 0.1);
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        }

        .details-title {
            color: #4c5f7a;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-row {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
            font-size: 0.9rem;
        }

        .detail-label {
            font-weight: 600;
            color: #4c5f7a;
            min-width: 120px;
        }

        .detail-value {
            color: #2d3748;
            font-weight: 500;
        }

        /* Equipment List Styles */
        .equipment-list {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .equipment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .equipment-item:last-child {
            margin-bottom: 0;
        }

        .equipment-name {
            font-weight: 600;
            color: #2d3748;
        }

        .equipment-qty {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Button Styles */
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
            text-decoration: none;
            display: inline-block;
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
            color: white;
            text-decoration: none;
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

        /* Modal Styles */
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
            .success-card, .error-card, .details-card {
                padding: 2rem 1.5rem;
            }
            
            .success-title {
                font-size: 2rem;
            }
            
            .success-icon, .error-icon {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }
            
            .detail-icon {
                margin-bottom: 0.5rem;
                margin-right: 0;
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
            <div class="col-lg-10">
                <div class="main-container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="user-avatar">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="page-title with-sparkle mb-2">Booking Confirmation</h2>
                        <p class="text-muted">Welcome, <?php echo htmlspecialchars($student_name); ?>! Here's your booking status.</p>
                    </div>

                    <?php if ($success): ?>
                        <!-- Success Card -->
                        <div class="success-card">
                            <div class="success-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <h2 class="success-title">Booking Submitted Successfully!</h2>
                            <p class="success-message">Your equipment booking request has been submitted and is pending approval from the administrator.</p>
                            
                            <div class="booking-id">
                                <div class="booking-id-label">Your Booking ID</div>
                                <div class="booking-id-value">#<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></div>
                            </div>
                            
                            <p class="text-muted">Please save this booking ID for your records. You can check the status of your booking in your dashboard.</p>
                        </div>

                        <!-- Booking Details Card -->
                        <div class="details-card">
                            <h4 class="details-title">
                                <i class="fas fa-info-circle"></i>
                                Booking Details
                            </h4>
                            
                            <div class="detail-row">
                                <div class="detail-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="detail-label">Event Name:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($booking_details['eventName']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="detail-label">Club:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($booking_details['clubName']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-icon">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="detail-label">Start Date:</div>
                                <div class="detail-value"><?php echo date('F j, Y', strtotime($booking_details['startDate'])); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-icon">
                                    <i class="fas fa-calendar-minus"></i>
                                </div>
                                <div class="detail-label">End Date:</div>
                                <div class="detail-value"><?php echo date('F j, Y', strtotime($booking_details['endDate'])); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-icon">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="detail-label">Equipment:</div>
                                <div class="detail-value">
                                    <div class="equipment-list">
                                        <?php foreach ($booking_details['equipment'] as $item): ?>
                                            <div class="equipment-item">
                                                <span class="equipment-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                                <span class="equipment-qty">Qty: <?php echo $item['qty']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Error Card -->
                        <div class="error-card">
                            <div class="error-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h2 class="error-title">Booking Failed</h2>
                            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <a href="user_dashboard.php" class="btn btn-primary me-3">
                            <i class="fas fa-tachometer-alt me-2"></i>Return to Dashboard
                        </a>
                        <?php if (!$success): ?>
                            <a href="addBooking.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Try Again
                            </a>
                        <?php else: ?>
                            <a href="user_view_booking.php" class="btn btn-secondary">
                                <i class="fas fa-list me-2"></i>View My Bookings
                            </a>
                        <?php endif; ?>
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
                Are you sure you want to logout?
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
