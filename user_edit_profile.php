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

// Fetch student data
$query = "SELECT * FROM user WHERE stud_num = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("s", $stud_num);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $student = $result->fetch_assoc();
} else {
    echo "<script>alert('Student profile not found.'); window.location.href='user_dashboard.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - UniEquip</title>
    
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

        .form-select-custom {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 400;
            background: white;
            color: #374151;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .form-select-custom:hover {
            border-color: #d1d5db;
        }

        .form-select-custom:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .form-select-custom option {
            background: white;
            color: #374151;
            padding: 8px;
        }

        .form-select-custom option:checked {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
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
                        <a class="nav-link modern-nav-link active" href="user_profile.php">
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
            <div class="col-lg-8">
                <div class="main-container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="user-avatar">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <h2 class="page-title with-sparkle mb-2">Edit Profile</h2>
                        <p class="text-muted">Update your personal information and account settings</p>
                    </div>

                    <!-- Edit Form -->
                    <form action="user_update_profile.php" method="post">
                        <div class="content-card">
                            <h4 class="section-title with-icon mb-4">Personal Information</h4>
                            
                            <!-- Student Number (Read-only) -->
                            <div class="form-field">
                                <div class="field-icon">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="field-label" for="stud_num">Student Number</label>
                                    <input type="text" 
                                           id="stud_num" 
                                           name="stud_num" 
                                           class="form-control-custom w-100" 
                                           value="<?php echo htmlspecialchars($student['stud_num']); ?>" 
                                           readonly
                                           style="background: #f1f3f4; cursor: not-allowed;">
                                    <small class="text-muted">This field cannot be changed</small>
                                </div>
                            </div>

                            <!-- Full Name -->
                            <div class="form-field">
                                <div class="field-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="field-label" for="stud_name">Full Name *</label>
                                    <input type="text" 
                                           id="stud_name" 
                                           name="stud_name" 
                                           class="form-control-custom w-100" 
                                           value="<?php echo htmlspecialchars($student['stud_name']); ?>" 
                                           required
                                           placeholder="Enter your full name">
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="form-field">
                                <div class="field-icon">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="field-label" for="stud_pass">Password *</label>
                                    <div class="position-relative">
                                        <input type="password" 
                                               id="stud_pass" 
                                               name="stud_pass" 
                                               class="form-control-custom w-100" 
                                               value="<?php echo htmlspecialchars($student['stud_pass']); ?>" 
                                               required
                                               placeholder="Enter your password">
                                        <button type="button" 
                                                class="btn btn-link position-absolute" 
                                                style="right: 10px; top: 50%; transform: translateY(-50%); color: #667eea;"
                                                onclick="togglePassword()">
                                            <i class="fas fa-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Telephone -->
                            <div class="form-field">
                                <div class="field-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="field-label" for="stud_tel">Telephone *</label>
                                    <input type="tel" 
                                           id="stud_tel" 
                                           name="stud_tel" 
                                           class="form-control-custom w-100" 
                                           value="<?php echo htmlspecialchars($student['stud_tel']); ?>" 
                                           required
                                           placeholder="Enter your phone number">
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="form-field">
                                <div class="field-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="field-label" for="stud_email">Email Address *</label>
                                    <input type="email" 
                                           id="stud_email" 
                                           name="stud_email" 
                                           class="form-control-custom w-100" 
                                           value="<?php echo htmlspecialchars($student['stud_email']); ?>" 
                                           required
                                           placeholder="Enter your email address">
                                </div>
                            </div>

                            <!-- Faculty -->
                            <div class="form-field">
                                <div class="field-icon">
                                    <i class="fas fa-university"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="field-label" for="faculty">Faculty *</label>
                                    <select id="faculty" 
                                            name="faculty" 
                                            class="form-select-custom w-100" 
                                            required 
                                            onchange="updateCourseCodes()">
                                        <option value="">-- Select Faculty --</option>
                                        <option value="FSKM" <?php echo ($student['faculty'] === 'FSKM') ? 'selected' : ''; ?>>FSKM</option>
                                        <option value="FSG" <?php echo ($student['faculty'] === 'FSG') ? 'selected' : ''; ?>>FSG</option>
                                        <option value="FP" <?php echo ($student['faculty'] === 'FP') ? 'selected' : ''; ?>>FP</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Course Code -->
                            <div class="form-field">
                                <div class="field-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="field-label" for="course_code">Course Code *</label>
                                    <select id="course_code" 
                                            name="course_code" 
                                            class="form-select-custom w-100" 
                                            required>
                                        <option value="">-- Select Faculty First --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center">
                            <a href="user_profile.php" class="btn btn-secondary me-3">
                                <i class="fas fa-arrow-left me-2"></i>Back to Profile
                            </a>
                            <button type="submit" class="btn btn-custom">
                                <i class="fas fa-save me-2"></i>Save Changes
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
                Are you sure you want to logout? Any unsaved changes will be lost.
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

    <!-- Custom JavaScript -->
    <script>
        // Course update function based on faculty selection
        function updateCourseCodes() {
            const faculty = document.getElementById("faculty").value;
            const courseSelect = document.getElementById("course_code");
            const currentCourse = "<?php echo htmlspecialchars($student['course_code']); ?>";

            const courseOptions = {
                FSKM: ["CS110", "CS111", "CS112", "CS143", "CS230"],
                FSG: ["AS120"],
                FP: ["AC120", "AC110"]
            };

            courseSelect.innerHTML = "";

            if (courseOptions[faculty]) {
                courseOptions[faculty].forEach(code => {
                    const option = document.createElement("option");
                    option.value = code;
                    option.text = code.toUpperCase();
                    if (code.toUpperCase() === currentCourse.toUpperCase()) {
                        option.selected = true;
                    }
                    courseSelect.appendChild(option);
                });
            } else {
                const option = document.createElement("option");
                option.value = "";
                option.text = "Select Faculty First";
                courseSelect.appendChild(option);
            }
        }

        function togglePassword() {
            const passwordField = document.getElementById('stud_pass');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        // Logout Modal Functions
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
        
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize course codes on page load
            updateCourseCodes();

            const form = document.querySelector('form[action="user_update_profile.php"]');
            const inputs = document.querySelectorAll('.form-control-custom, .form-select-custom');
            
            // Add focus effects
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.parentElement.style.background = 'rgba(102, 126, 234, 0.08)';
                    this.parentElement.parentElement.style.borderRadius = '15px';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.parentElement.style.background = '';
                    this.parentElement.parentElement.style.borderRadius = '';
                });
            });
            
            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                const submitBtn = document.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                submitBtn.disabled = true;
            });
            
            // Close modal with Escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    hideLogoutModal();
                }
            });
        });
    </script>
</body>
</html>
