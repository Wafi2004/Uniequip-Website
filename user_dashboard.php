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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - UniEquip</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- User Common Styles -->
    <link href="user_style.css" rel="stylesheet">
    
    <style>
        /* Modern Navbar Styles */
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

        /* Dashboard Specific Styles */
        .welcome-card {
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            border-radius: 25px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 70px rgba(102, 126, 234, 0.4);
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        .welcome-card h2 {
            position: relative;
            z-index: 1;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .welcome-card p {
            position: relative;
            z-index: 1;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
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

        .dashboard-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .card-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }

        .dashboard-card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* Different gradient colors for each card */
        .dashboard-card:nth-child(1) .card-icon {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .dashboard-card:nth-child(2) .card-icon {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .dashboard-card:nth-child(3) .card-icon {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
        }

        .dashboard-card:nth-child(4) .card-icon {
            background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #4c5f7a;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .card-description {
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .card-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            text-decoration: none;
            display: inline-block;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .card-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .card-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
            color: white;
            text-decoration: none;
        }

        .card-btn:hover::before {
            left: 100%;
        }

        /* Different button colors for each card */
        .dashboard-card:nth-child(2) .card-btn {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .dashboard-card:nth-child(2) .card-btn:hover {
            box-shadow: 0 12px 35px rgba(245, 87, 108, 0.5);
        }

        .dashboard-card:nth-child(3) .card-btn {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
        }

        .dashboard-card:nth-child(3) .card-btn:hover {
            box-shadow: 0 12px 35px rgba(78, 205, 196, 0.5);
        }

        .dashboard-card:nth-child(4) .card-btn {
            background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
            color: #2d3748;
        }

        .dashboard-card:nth-child(4) .card-btn:hover {
            box-shadow: 0 12px 35px rgba(255, 234, 167, 0.5);
            color: #2d3748;
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
            
            .dashboard-cards {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .welcome-card {
                padding: 2rem 1.5rem;
            }
            
            .dashboard-card {
                padding: 2rem 1.5rem;
            }
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

        /* Admin Dashboard Style Modal - Exact Copy */
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
                        <a class="nav-link modern-nav-link active" href="user_dashboard.php">
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
                        <a class="nav-link modern-nav-link dropdown-toggle" href="#" id="bookingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="nav-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <span>Booking</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="bookingDropdown">
                            <li><a class="dropdown-item" href="addBooking.php">
                                <i class="fas fa-plus-circle me-2"></i>New Booking
                            </a></li>
                            <li><a class="dropdown-item" href="user_view_booking.php">
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
                    <!-- Welcome Section -->
                    <div class="welcome-card">
                        <h2>Welcome back, <?php echo htmlspecialchars($student_name); ?>! 👋</h2>
                        <p>Ready to manage your equipment bookings and explore available resources?</p>
                    </div>

                    <!-- Dashboard Cards -->
                    <div class="dashboard-cards">
                        <!-- Profile Card -->
                        <div class="dashboard-card">
                            <div class="card-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3 class="card-title">My Profile</h3>
                            <p class="card-description">
                                View and manage your personal information, contact details, and account settings.
                            </p>
                            <form action="user_profile.php" method="post" class="d-inline">
                                <button type="submit" class="card-btn">
                                    <i class="fas fa-eye me-2"></i>View Profile
                                </button>
                            </form>
                        </div>

                        <!-- Add Booking Card -->
                        <div class="dashboard-card">
                            <div class="card-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <h3 class="card-title">New Booking</h3>
                            <p class="card-description">
                                Book equipment for your academic needs. Browse available items and make reservations.
                            </p>
                            <form action="addBooking.php" method="post" class="d-inline">
                                <button type="submit" class="card-btn">
                                    <i class="fas fa-plus me-2"></i>Add Booking
                                </button>
                            </form>
                        </div>

                        <!-- View Bookings Card -->
                        <div class="dashboard-card">
                            <div class="card-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h3 class="card-title">My Bookings</h3>
                            <p class="card-description">
                                Track your current and past equipment bookings. Manage your reservations and history.
                            </p>
                            <form action="user_view_booking.php" method="post" class="d-inline">
                                <button type="submit" class="card-btn">
                                    <i class="fas fa-list me-2"></i>View Bookings
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Stats or Additional Info -->
                    <div class="content-card">
                        <div class="row text-center">
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="field-icon me-3">
                                        <i class="fas fa-tools"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Equipment Available</h5>
                                        <p class="text-muted mb-0">Ready for booking</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="field-icon me-3">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">24/7 Access</h5>
                                        <p class="text-muted mb-0">Book anytime</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="field-icon me-3">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Student ID: <?php echo htmlspecialchars($stud_num); ?></h5>
                                        <p class="text-muted mb-0">Your identifier</p>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                Are you sure you want to logout? You will need to login again to access your dashboard.
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
