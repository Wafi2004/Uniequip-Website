<?php
session_start();
include "db.php";

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

// Fetch club names from the database where type is 'Open' OR 'Close' and status is 'Active'
$clubs = [];
$result = $connect->query("SELECT club_name, type FROM club WHERE (type = 'Open' OR type = 'Close') AND status = 'Active' ORDER BY club_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clubs[] = [
            'name' => $row['club_name'],
            'type' => $row['type']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Booking - UniEquip</title>
    
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

        /* Form Specific Styles */
        .form-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #4c5f7a;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .label-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: #667eea;
        }

        .form-control {
            border: 2px solid rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
            transform: translateY(-2px);
        }

        .form-control:hover {
            border-color: rgba(102, 126, 234, 0.3);
            background: rgba(255, 255, 255, 0.9);
        }

        .form-select {
            border: 2px solid rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 15px 20px;
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

        .form-select:hover {
            border-color: rgba(102, 126, 234, 0.3);
            background: rgba(255, 255, 255, 0.9);
        }

        .form-select:disabled {
            background: rgba(248, 250, 252, 0.8);
            color: #64748b;
            cursor: not-allowed;
        }

        /* Alert Styles */
        .alert-info {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border: none;
            border-radius: 15px;
            color: white;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }

        .alert-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            border-radius: 15px;
            color: white;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
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

        .btn-primary:disabled {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 5px 15px rgba(148, 163, 184, 0.3);
        }

        .btn-primary:disabled:hover {
            transform: none;
            box-shadow: 0 5px 15px rgba(148, 163, 184, 0.3);
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
            
            .form-container {
                padding: 0 1rem;
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
                            <li><a class="dropdown-item active" href="addBooking.php">
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
            <div class="col-lg-10">
                <div class="main-container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="user-avatar">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h2 class="page-title with-sparkle mb-2">Add Event Booking</h2>
                        <p class="text-muted">Welcome, <?php echo htmlspecialchars($student_name); ?>! Create a new equipment booking for your event.</p>
                    </div>

                    <!-- Booking Form -->
                    <div class="content-card">
                        <div class="form-container">
                            <?php if (empty($clubs)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>No Active Clubs Available</strong><br>
                                    Unfortunately, there are no active clubs available for booking at this time. Please contact the administrator or try again later.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Booking Information</strong><br>
                                    Fill out the form below to create a new equipment booking for your event. All fields are required.
                                </div>
                            <?php endif; ?>

                            <form action="userEquipment.php" method="post" id="bookingForm">
                                <input type="hidden" name="stud_num" value="<?php echo htmlspecialchars($_SESSION['user_number']); ?>">

                                <!-- Event Name -->
                                <div class="form-group">
                                    <label for="eventName" class="form-label">
                                        <span class="label-icon"><i class="fas fa-calendar-alt"></i></span>
                                        Event Name
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="eventName" 
                                           name="eventName" 
                                           placeholder="Enter the name of your event"
                                           required>
                                </div>

                                <!-- Date Range -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="startDate" class="form-label">
                                                <span class="label-icon"><i class="fas fa-calendar-plus"></i></span>
                                                Start Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="startDate" 
                                                   name="startDate" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="endDate" class="form-label">
                                                <span class="label-icon"><i class="fas fa-calendar-minus"></i></span>
                                                End Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="endDate" 
                                                   name="endDate" 
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Club Selection -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="clubType" class="form-label">
                                                <span class="label-icon"><i class="fas fa-tags"></i></span>
                                                Club Type
                                            </label>
                                            <select class="form-select" 
                                                    id="clubType" 
                                                    name="clubType" 
                                                    required 
                                                    onchange="filterClubs()">
                                                <option value="">-- Select Club Type --</option>
                                                <option value="Open">Open Club</option>
                                                <option value="Close">Close Club</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="clubName" class="form-label">
                                                <span class="label-icon"><i class="fas fa-users"></i></span>
                                                Club Name
                                            </label>
                                            <select class="form-select" 
                                                    id="clubName" 
                                                    name="clubName" 
                                                    required 
                                                    disabled>
                                                <option value="">-- First Select Club Type --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="text-center mt-4">
                                    <a href="user_dashboard.php" class="btn btn-secondary me-3">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                    </a>
                                    <button type="submit" 
                                            class="btn btn-primary" 
                                            <?php echo empty($clubs) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-arrow-right me-2"></i>Next: Select Equipment
                                    </button>
                                </div>
                            </form>
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
                    <p class="mb-1 text-muted">&copy; 2025 UniEquip. All rights reserved.</p>
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
        // Store all clubs data for filtering
        const allClubs = <?php echo json_encode($clubs); ?>;

        function filterClubs() {
            const clubType = document.getElementById('clubType').value;
            const clubNameSelect = document.getElementById('clubName');
            
            // Clear existing options
            clubNameSelect.innerHTML = '<option value="">-- Select Club --</option>';
            
            if (clubType === '') {
                clubNameSelect.disabled = true;
                clubNameSelect.innerHTML = '<option value="">-- First Select Club Type --</option>';
                return;
            }
            
            // Filter clubs based on selected type
            const filteredClubs = allClubs.filter(club => club.type === clubType);
            
            if (filteredClubs.length === 0) {
                clubNameSelect.disabled = true;
                clubNameSelect.innerHTML = '<option value="">-- No ' + clubType + ' clubs available --</option>';
                return;
            }
            
            // Enable dropdown and add filtered clubs
            clubNameSelect.disabled = false;
            filteredClubs.forEach(club => {
                const option = document.createElement('option');
                option.value = club.name;
                option.textContent = club.name;
                clubNameSelect.appendChild(option);
            });
        }

        // Set minimum date to today
        document.getElementById('startDate').min = new Date().toISOString().split('T')[0];
        document.getElementById('endDate').min = new Date().toISOString().split('T')[0];

        // Ensure end date is not before start date
        document.getElementById('startDate').addEventListener('change', function() {
            document.getElementById('endDate').min = this.value;
            if (document.getElementById('endDate').value && document.getElementById('endDate').value < this.value) {
                document.getElementById('endDate').value = this.value;
            }
        });

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