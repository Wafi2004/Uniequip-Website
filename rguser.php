<?php
include("db.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST["submit"])) {
    $student_name = $_POST['student_name'];
    $student_number = $_POST['student_number'];
    $password = $_POST['password'];
    $student_tel = $_POST['student_tel'];
    $student_email = $_POST['student_email'];
    $course_code = $_POST['course_code'];
    $faculty = $_POST['faculty'];

    if (
        empty($student_name) || empty($student_number) || empty($password) ||
        empty($student_tel) || empty($student_email) || empty($course_code) || empty($faculty)
    ) {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
        exit;
    }

    $stmt = $connect->prepare("SELECT * FROM user WHERE stud_num = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $check = $stmt->get_result();

    if (!$check) {
        echo "<script>alert('Query error: " . $connect->error . "'); window.history.back();</script>";
        exit;
    }

    if ($check->num_rows > 0) {
        echo "<script>alert('Student number already exists.'); window.history.back();</script>";
        exit;
    }
    $stmt->close();

    $insert_stmt = $connect->prepare("INSERT INTO user 
        (stud_name, stud_num, stud_pass, stud_tel, stud_email, course_code, faculty) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("sssssss", $student_name, $student_number, $password, $student_tel, $student_email, $course_code, $faculty);
    $insert = $insert_stmt->execute();

    if ($insert) {
        echo "<script>alert('Registration successful. Redirecting to login...'); window.location.href = 'login.php';</script>";
    } else {
        echo "<script>alert('Error inserting data: " . $connect->error . "'); window.history.back();</script>";
    }
    $insert_stmt->close();
}
?>

<html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - UniEquip</title>
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

        .registration-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            animation: slideInUp 1s ease-out;
            position: relative;
            overflow: hidden;
            max-width: 550px;
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

        .form-select {
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
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-select option {
            background: white;
            color: var(--text-primary);
            padding: 8px;
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
            
            .registration-card {
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
            .registration-card {
                padding: 24px 16px;
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
    </style>
    <script>
        function updateCourseCodes() {
            const faculty = document.getElementById("faculty").value;
            const courseSelect = document.getElementById("course_code");

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
                    courseSelect.appendChild(option);
                });
            } else {
                const option = document.createElement("option");
                option.value = "";
                option.text = "Select Faculty First";
                courseSelect.appendChild(option);
            }
        }
    </script>
</head>
<body>
    <div class="animated-bg"></div>
    
    <div class="particles">
        <!-- Particles will be generated by JavaScript -->
    </div>

    <div class="main-container">
        <div class="registration-card loading">
            <div class="header-section">
                <div class="logo-container">
                    <img src="uitm.png" alt="UiTM Logo" class="uitm-logo">
                </div>
                <h2 class="page-title">Student Registration</h2>
                <p class="page-subtitle">Create your student account to book equipment for events</p>
            </div>

            <div class="form-section">
                <form action="" method="post">
                    <div class="form-group">
                        <label for="student_name">Student Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="student_name" name="student_name" class="form-input" placeholder="Student Name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="student_number">Student Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-id-card input-icon"></i>
                            <input type="text" id="student_number" name="student_number" class="form-input" placeholder="Student Number" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-input" placeholder="Password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="student_tel">Phone Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="text" id="student_tel" name="student_tel" class="form-input" placeholder="Phone Number" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="student_email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="student_email" name="student_email" class="form-input" placeholder="Email Address" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="faculty">Select Faculty:</label>
                        <div class="input-wrapper">
                            <i class="fas fa-university input-icon"></i>
                            <select name="faculty" id="faculty" class="form-select" required onchange="updateCourseCodes()">
                                <option value="">-- Select Faculty --</option>
                                <option value="FSKM">FSKM</option>
                                <option value="FSG">FSG</option>
                                <option value="FP">FP</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="course_code">Select Course Code:</label>
                        <div class="input-wrapper">
                            <i class="fas fa-book input-icon"></i>
                            <select name="course_code" id="course_code" class="form-select" required>
                                <option value="">-- Select Faculty First --</option>
                            </select>
                        </div>
                    </div>

                    <div class="button-group">
                        <input type="submit" name="submit" value="Submit" class="form-btn submit-btn">
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
        document.querySelectorAll('.form-input, .form-select').forEach(input => {
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
    </script>
</body>
</html>
