<?php
session_start();
include("db.php");

// Show all errors for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST["submit"])) {
    $user_number = trim($_POST['user_number']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Check for empty fields
    if (empty($user_number) || empty($password) || empty($role)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showErrorAlert('Please fill in all fields and select a role.');
            });
        </script>";
        exit;
    }

    if ($role == "student") {
        $stmt = $connect->prepare("SELECT * FROM user WHERE stud_num = ? AND stud_pass = ?");
        $stmt->bind_param("ss", $user_number, $password);
        $stmt->execute();
        $query = $stmt->get_result();
        if ($query->num_rows == 1) {
            $_SESSION['user_number'] = $user_number;
            $_SESSION['role'] = "student";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showSuccessAlert('Welcome! Redirecting to your dashboard...', 'user_dashboard.php');
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showErrorAlert('Invalid student number or password. Please try again.');
                });
            </script>";
        }
        $stmt->close();
    } elseif ($role == "admin") {
        $stmt = $connect->prepare("SELECT * FROM admin WHERE staff_num = ? AND staff_password = ?");
        $stmt->bind_param("ss", $user_number, $password);
        $stmt->execute();
        $query = $stmt->get_result();
        if ($query->num_rows == 1) {
            $row = $query->fetch_assoc();
            $_SESSION['user_number'] = $user_number;
            $_SESSION['staff_name'] = $row['staff_name'];
            $_SESSION['role'] = "admin";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showSuccessAlert('Welcome back, " . $row['staff_name'] . "! Redirecting to admin dashboard...', 'admin_dashboard.php');
                });
            </script>";
            $stmt->close();
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showErrorAlert('Invalid admin number or password. Please try again.');
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showErrorAlert('Invalid role selected. Please choose Student or Admin.');
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UniEquip</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --accent-color: #06b6d4;
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --dark-bg: #0f172a;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --success-color: #10b981;
            --error-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark-bg);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Floating Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            animation: slideInUp 1s ease-out;
            position: relative;
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        @keyframes slideInUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }


        .header-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .uitm-logo {
            width: 80px;
            height: auto;
            object-fit: contain;
            margin-bottom: 12px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
        }

        .page-title {
            color: var(--text-primary);
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .form-section {
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1rem;
            z-index: 1;
        }

        .form-input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            border: 2px solid rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 400;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            color: var(--text-primary);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
            opacity: 0.8;
        }

        .role-selection {
            margin-bottom: 20px;
        }

        .role-options {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .role-option {
            flex: 1;
            position: relative;
        }

        .role-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .role-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px 20px;
            border: 2px solid rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .role-label:hover {
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.95);
        }

        .role-radio:checked + .role-label {
            border-color: var(--primary-color);
            background: var(--gradient-1);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .role-icon {
            font-size: 1rem;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .form-btn {
            flex: 1;
            padding: 16px 24px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn {
            background: var(--gradient-1);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .reset-btn {
            background: transparent;
            color: var(--text-secondary);
            border: 2px solid rgba(100, 116, 139, 0.3);
        }

        .reset-btn:hover {
            background: rgba(100, 116, 139, 0.1);
            border-color: var(--text-secondary);
            transform: translateY(-2px);
        }

        .back-link {
            text-align: center;
            margin-top: 24px;
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: var(--secondary-color);
            transform: translateX(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 16px;
            }
            
            .login-card {
                padding: 32px 24px;
            }
            
            .uitm-logo {
                width: 64px;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 24px 16px;
            }
            
            .role-options {
                flex-direction: column;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .form-btn {
                padding: 14px 20px;
                font-size: 0.95rem;
            }
        }

        /* Loading Animation */
        .loading {
            opacity: 0;
            animation: fadeIn 0.5s ease-out 0.2s forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        /* Custom Alert Styles */
        .custom-alert {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .custom-alert.show {
            opacity: 1;
            visibility: visible;
        }

        .alert-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transform: scale(0.8) translateY(50px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .custom-alert.show .alert-content {
            transform: scale(1) translateY(0);
        }

        .alert-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-1);
        }

        .alert-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
            animation: bounceIn 0.6s ease-out 0.2s both;
        }

        @keyframes bounceIn {
            0% { transform: scale(0) rotate(180deg); opacity: 0; }
            50% { transform: scale(1.2) rotate(180deg); }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }

        .alert-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .alert-message {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .alert-button {
            background: var(--gradient-1);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .alert-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        /* Success Alert Specific Styles */
        .alert-success .alert-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .alert-success .alert-button {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .alert-success .alert-button:hover {
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        /* Error Alert Specific Styles */
        .alert-error .alert-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .alert-error .alert-button {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .alert-error .alert-button:hover {
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }
    </style>
    <script>
        function validateForm() {
            const number = document.forms["loginForm"]["user_number"].value.trim();
            const password = document.forms["loginForm"]["password"].value.trim();
            const role = document.forms["loginForm"]["role"];

            if (number === "" || password === "") {
                alert("Please fill in all fields.");
                return false;
            }

            let roleSelected = false;
            for (let i = 0; i < role.length; i++) {
                if (role[i].checked) {
                    roleSelected = true;
                    break;
                }
            }

            if (!roleSelected) {
                alert("Please select a role.");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div class="animated-bg"></div>
    
    <div class="particles">
        <!-- Particles will be generated by JavaScript -->
    </div>

    <div class="main-container">
        <div class="login-card loading">
            <div class="header-section">
                <div class="logo-container">
                    <img src="uitm.png" alt="UiTM Logo" class="uitm-logo">
                </div>
                <h2 class="page-title">Welcome Back</h2>
                <p class="page-subtitle">Sign in to access your UniEquip account</p>
            </div>

            <div class="form-section">
                <form name="loginForm" action="" method="post" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="user_number">Student / Staff Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-id-card input-icon"></i>
                            <input type="text" id="user_number" name="user_number" class="form-input" placeholder="Enter your ID number" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <div class="role-selection">
                        <label>Select Your Role</label>
                        <div class="role-options">
                            <div class="role-option">
                                <input type="radio" id="student" name="role" value="student" class="role-radio">
                                <label for="student" class="role-label">
                                    <i class="fas fa-graduation-cap role-icon"></i>
                                    Student
                                </label>
                            </div>
                            <div class="role-option">
                                <input type="radio" id="admin" name="role" value="admin" class="role-radio">
                                <label for="admin" class="role-label">
                                    <i class="fas fa-user-cog role-icon"></i>
                                    Admin
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="button-group">
                        <input type="submit" name="submit" value="Sign In" class="form-btn submit-btn">
                        <input type="reset" value="Reset" class="form-btn reset-btn">
                    </div>
                </form>
            </div>

            <div class="back-link">
                <a href="index.html">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Generate floating particles
        function createParticles() {
            const particlesContainer = document.querySelector('.particles');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }
        
        // Initialize particles when page loads
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
        });
        
        // Add form interaction effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Button hover effects
        document.querySelectorAll('.form-btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.02)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Role selection animation
        document.querySelectorAll('.role-label').forEach(label => {
            label.addEventListener('mouseenter', function() {
                if (!this.previousElementSibling.checked) {
                    this.style.transform = 'translateY(-2px)';
                }
            });
            
            label.addEventListener('mouseleave', function() {
                if (!this.previousElementSibling.checked) {
                    this.style.transform = 'translateY(0)';
                }
            });
        });

        // Custom Alert Functions
        function showCustomAlert(type, title, message, callback) {
            const alertHtml = `
                <div class="custom-alert alert-${type}" id="customAlert">
                    <div class="alert-content">
                        <div class="alert-icon">
                            <i class="fas ${type === 'success' ? 'fa-check' : 'fa-exclamation-triangle'}"></i>
                        </div>
                        <h3 class="alert-title">${title}</h3>
                        <p class="alert-message">${message}</p>
                        <button class="alert-button" onclick="closeCustomAlert(${callback ? 'true' : 'false'})">${type === 'success' ? 'Continue' : 'OK'}</button>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', alertHtml);
            
            setTimeout(() => {
                document.getElementById('customAlert').classList.add('show');
            }, 100);
        }

        function closeCustomAlert(hasCallback) {
            const alert = document.getElementById('customAlert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => {
                    alert.remove();
                    if (hasCallback) {
                        // Execute callback after alert is closed
                        if (window.alertCallback) {
                            window.alertCallback();
                        }
                    }
                }, 300);
            }
        }

        // Override default alert for success messages
        function showSuccessAlert(message, redirectUrl) {
            window.alertCallback = function() {
                window.location.href = redirectUrl;
            };
            showCustomAlert('success', 'Login Successful!', message, true);
        }

        // Override default alert for error messages
        function showErrorAlert(message) {
            showCustomAlert('error', 'Login Failed', message, false);
        }
    </script>
</body>
</html>

