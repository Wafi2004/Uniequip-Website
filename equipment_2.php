<?php
session_start();
include("db.php");

// Show errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if student is logged in
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'student') {
    echo "<script>alert('Access denied. Please login as student.'); window.location.href='login.php';</script>";
    exit();
}

// Default empty values
$selected_date = '';
$selected_category = '';
$equipment = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_date = $_POST['date'];
    $selected_category = trim($_POST['category']);

    // Only search if date is provided
    if (!empty($selected_date)) {
        $query = "
            SELECT e.id_equipment, e.name, e.category, e.model, e.qty, e.picture
            FROM equipment e
            WHERE e.status = 'Available'
            AND e.id_equipment NOT IN (
                SELECT be.id_equipment
                FROM booking_equipment be
                JOIN booking b ON be.id_booking = b.id_booking
                WHERE ? BETWEEN b.start_date AND b.end_date
            )
        ";

        // If category is selected, add filter
        if (!empty($selected_category)) {
            $query .= " AND e.category = ?";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("ss", $selected_date, $selected_category);
        } else {
            $stmt = $connect->prepare($query);
            $stmt->bind_param("s", $selected_date);
        }

        $stmt->execute();
        $equipment = $stmt->get_result();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Equipment - UniEquip</title>
    
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

        /* Equipment Specific Styles */
        .search-form {
            background: linear-gradient(135deg, #f8faff, #f0f4ff);
            border-radius: 25px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(102, 126, 234, 0.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .search-form::before {
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

        .search-form h3 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .search-form h3::before {
            content: '🔍';
            margin-right: 10px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .form-group label {
            font-weight: 600;
            color: #4c5f7a;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            border: 2px solid rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }

        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .equipment-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .equipment-card::before {
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

        .equipment-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .equipment-image {
            width: 100%;
            height: 200px;
            border-radius: 15px;
            object-fit: cover;
            margin-bottom: 1.5rem;
            border: 3px solid rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .equipment-card:hover .equipment-image {
            border-color: #667eea;
            transform: scale(1.05);
        }

        .no-image {
            width: 100%;
            height: 200px;
            border-radius: 15px;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            border: 3px solid rgba(102, 126, 234, 0.1);
            color: #64748b;
            font-size: 3rem;
            position: relative;
            z-index: 1;
        }

        .equipment-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #4c5f7a;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .equipment-details {
            position: relative;
            z-index: 1;
        }

        .equipment-detail {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            padding: 0.5rem 0;
        }

        .detail-icon {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
            font-size: 0.9rem;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            color: #2d3748;
            font-weight: 500;
        }

        .quantity-badge {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: #64748b;
            position: relative;
            z-index: 1;
        }

        .no-results-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .equipment-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .search-form {
                padding: 2rem 1.5rem;
            }
            
            .equipment-card {
                padding: 1.5rem;
            }
            
            .equipment-image,
            .no-image {
                height: 150px;
            }
            
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
                        <a class="nav-link modern-nav-link active" href="available_equipment.php">
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
            <div class="col-lg-11">
                <div class="main-container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <h1 class="page-title with-sparkle">Available Equipment</h1>
                        <p class="text-muted">Search and browse available equipment by date and category</p>
                    </div>

                    <!-- Search Form -->
                    <div class="search-form">
                        <h3>Search Equipment</h3>
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date">
                                            <i class="fas fa-calendar-alt me-2"></i>Select Date
                                        </label>
                                        <input type="date" 
                                               name="date" 
                                               id="date" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($selected_date); ?>" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category">
                                            <i class="fas fa-tags me-2"></i>Category (Optional)
                                        </label>
                                        <input type="text" 
                                               name="category" 
                                               id="category" 
                                               class="form-control" 
                                               placeholder="e.g., Laptop, Camera, Projector..."
                                               value="<?php echo htmlspecialchars($selected_category); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn-custom">
                                    <i class="fas fa-search me-2"></i>Search Equipment
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Results Section -->
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                        <?php if (!empty($selected_date)): ?>
                            <div class="content-card">
                                <h4 class="section-title with-icon mb-4">
                                    Search Results for <?php echo htmlspecialchars($selected_date); ?>
                                    <?php if (!empty($selected_category)): ?>
                                        <span class="text-muted">in category: <?php echo htmlspecialchars($selected_category); ?></span>
                                    <?php endif; ?>
                                </h4>
                                
                                <?php if ($equipment->num_rows > 0): ?>
                                    <div class="equipment-grid">
                                        <?php while ($row = $equipment->fetch_assoc()): ?>
                                            <div class="equipment-card">
                                                  <?php if (!empty($row['picture']) && file_exists($row['picture'])): ?>
    <img src="<?php echo htmlspecialchars($row['picture']); ?>" 
         alt="<?php echo htmlspecialchars($row['name']); ?>" 
         class="equipment-image"
         onclick="openImageWindow('<?php echo htmlspecialchars($row['picture']); ?>')">
<?php else: ?>
    <div class="no-image">
        <i class="fas fa-image"></i>
    </div>
<?php endif; ?>
                                                
                                                <h5 class="equipment-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                                
                                                <div class="equipment-details">
                                                    <div class="equipment-detail">
                                                        <div class="detail-icon">
                                                            <i class="fas fa-tag"></i>
                                                        </div>
                                                        <div class="detail-content">
                                                            <div class="detail-label">Equipment ID</div>
                                                            <div class="detail-value"><?php echo htmlspecialchars($row['id_equipment']); ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="equipment-detail">
                                                        <div class="detail-icon">
                                                            <i class="fas fa-layer-group"></i>
                                                        </div>
                                                        <div class="detail-content">
                                                            <div class="detail-label">Category</div>
                                                            <div class="detail-value"><?php echo htmlspecialchars($row['category']); ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="equipment-detail">
                                                        <div class="detail-icon">
                                                            <i class="fas fa-cog"></i>
                                                        </div>
                                                        <div class="detail-content">
                                                            <div class="detail-label">Model</div>
                                                            <div class="detail-value"><?php echo htmlspecialchars($row['model'] ?? ''); ?></div>

                                                        </div>
                                                    </div>
                                                    
                                                    <div class="equipment-detail">
                                                        <div class="detail-icon">
                                                            <i class="fas fa-boxes"></i>
                                                        </div>
                                                        <div class="detail-content">
                                                            <div class="detail-label">Available Quantity</div>
                                                            <div class="detail-value">
                                                                <span class="quantity-badge">
                                                                    <?php echo htmlspecialchars($row['qty']); ?> units
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-results">
                                        <div class="no-results-icon">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        <h5>No Equipment Available</h5>
                                        <p>No equipment is available on <strong><?php echo htmlspecialchars($selected_date); ?></strong>
                                        <?php if (!empty($selected_category)): ?>
                                            in the <strong><?php echo htmlspecialchars($selected_category); ?></strong> category
                                        <?php endif; ?>.</p>
                                        <p class="text-muted">Try searching with a different date or category.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="content-card">
                                <div class="no-results">
                                    <div class="no-results-icon">
                                        <i class="fas fa-calendar-times"></i>
                                    </div>
                                    <h5>Please Select a Date</h5>
                                    <p>Please select a date to search for available equipment.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
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

        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date');
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        });
    </script>
</body>
</html>