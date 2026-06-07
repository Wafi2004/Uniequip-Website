<?php
session_start();

// Admin access only
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include("db.php");

// Show all errors (for debugging only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $qty = intval($_POST['qty']);
    $model = !empty($_POST['model']) ? trim($_POST['model']) : null;
    $status = "Available";
   

    // Validate required fields
    if (empty($name) || empty($category) || $qty <= 0) {
        echo "<script>alert('Please fill in all required fields with valid data.'); window.history.back();</script>";
        exit;
    }

    // Check for duplicate name
    $stmt = $connect->prepare("SELECT * FROM equipment WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $check = $stmt->get_result();
    if ($check->num_rows > 0) {
        echo "<script>alert('Equipment name already exists.'); window.history.back();</script>";
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Handle picture upload if exists
    $picturePath = null;
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['picture']['tmp_name'];
        $fileName = $_FILES['picture']['name'];
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($fileExt), $allowedExtensions)) {
            echo "<script>alert('Only image files (JPG, JPEG, PNG, GIF) are allowed.'); window.history.back();</script>";
            exit;
        }

        $newFileName = uniqid("eqp_", true) . '.' . $fileExt;
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = $uploadDir . $newFileName;
        if (!move_uploaded_file($fileTmpPath, $destPath)) {
            echo "<script>alert('Failed to upload the image.'); window.history.back();</script>";
            exit;
        }

        $picturePath = $destPath;
    }

    // Insert into DB
    $stmt = mysqli_prepare($connect, "INSERT INTO equipment (name, category, status, qty, model, picture) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssiss", $name, $category, $status, $qty, $model, $picturePath);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Equipment added successfully.'); window.location.href='admin_view_equipment.php';</script>";
    } else {
        echo "<script>alert('Failed to add equipment.'); window.history.back();</script>";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($connect);
    exit;
}
?>

<!-- Display Form on GET -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Equipment - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
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
                            <button type="submit" class="nav-link active">
                                <div class="nav-icon"><i class="fas fa-tools"></i></div>
                                View Equipment
                            </button>
                        </form>
                    </div>
                    <div class="nav-item">
                        <form action="admin_view_all_record.php" method="post" style="margin: 0;">
                            <button type="submit" class="nav-link">
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
                        <form action="logout.php" method="post" style="margin: 0;">
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
                        <span class="breadcrumb-item">Equipment</span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Add New Equipment</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-actions">
                        <form action="logout.php" method="post" style="margin: 0;">
                            <button type="submit" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </button>
                        </form>
                        <div class="user-menu">
                            <div class="user-avatar"><?= strtoupper(substr($_SESSION['staff_name'], 0, 2)) ?></div>
                            <div class="user-info">
                                <div class="user-name"><?= htmlspecialchars($_SESSION['staff_name']) ?></div>
                                <div class="user-role">Administrator</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Add Equipment Form -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-plus-circle" style="margin-right: 8px; color: var(--primary-color);"></i>
                            Add New Equipment
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-info-circle"></i>
                            Fill in the details below
                        </div>
                    </div>

                    <div style="padding: 32px;">
                        <form method="post" enctype="multipart/form-data" style="max-width: 600px;">
                            <div style="display: grid; gap: 24px;">
                                <!-- Equipment Name -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-tag" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Equipment Name *
                                    </label>
                                    <input type="text" name="name" class="form-input" required 
                                           placeholder="Enter equipment name" 
                                           style="padding: 12px 16px; font-size: 14px;">
                                </div>

                                <!-- Category -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-list" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Category *
                                    </label>
                                    <select name="category" class="form-select" required 
                                            style="padding: 12px 16px; font-size: 14px;">
                                        <option value="">-- Select Category --</option>
                                        <?php
                                        $categories = [
                                            'Stage Equipment', 'Audio Equipment', 'Visual Equipment', 'Lighting Equipment',
                                            'Furniture & Seating', 'Tents & Canopies', 'Decor & Draping', 'Power & Electrical',
                                            'Staging & Structures', 'Signage & Display', 'Catering Equipment', 'Climate Control',
                                            'Event Technology', 'Sanitation & Safety', 'Transportation & Storage'
                                        ];
                                        foreach ($categories as $cat) {
                                            echo "<option value=\"$cat\">$cat</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calculator" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Quantity *
                                    </label>
                                    <input type="number" name="qty" class="form-input" min="1" required 
                                           placeholder="Enter quantity" 
                                           style="padding: 12px 16px; font-size: 14px;">
                                </div>

                                <!-- Model -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-barcode" style="margin-right: 6px; color: var(--text-muted);"></i>
                                        Model <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span>
                                    </label>
                                    <input type="text" name="model" class="form-input" 
                                           placeholder="Enter model number or description" 
                                           style="padding: 12px 16px; font-size: 14px;">
                                </div>

                                <!-- Picture Upload -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-image" style="margin-right: 6px; color: var(--text-muted);"></i>
                                        Equipment Picture <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span>
                                    </label>
                                    <div style="border: 2px dashed var(--border-color); border-radius: var(--radius-md); padding: 24px; text-align: center; background-color: var(--bg-primary);">
                                        <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: var(--text-muted); margin-bottom: 12px;"></i>
                                        <div style="margin-bottom: 12px;">
                                            <input type="file" name="picture" accept="image/*" 
                                                   style="display: none;" id="picture-upload">
                                            <label for="picture-upload" class="btn btn-secondary" style="cursor: pointer;">
                                                <i class="fas fa-folder-open"></i>
                                                Choose Image File
                                            </label>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-muted);">
                                            Supported formats: JPG, JPEG, PNG, GIF
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div style="display: flex; gap: 12px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                                    <button type="submit" class="btn btn-primary" style="min-width: 140px;">
                                        <i class="fas fa-save"></i>
                                        Add Equipment
                                    </button>
                                    <a href="admin_view_equipment.php" class="btn btn-secondary" style="min-width: 140px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                                        <i class="fas fa-times"></i>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show selected file name
        document.getElementById('picture-upload').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                const label = document.querySelector('label[for="picture-upload"]');
                label.innerHTML = '<i class="fas fa-check"></i> ' + fileName;
                label.style.backgroundColor = 'var(--accent-color)';
                label.style.color = 'white';
            }
        });
    </script>
</body>
</html>
