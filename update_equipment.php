<?php
session_start();

// Restrict access to admins only
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include("db.php");

// Function to calculate borrowed quantity based on current bookings
function calculateBorrowedQty($connect, $id_equipment) {
    $today = date('Y-m-d');
    
    $query = "SELECT COALESCE(SUM(be.qty), 0) as total_borrowed 
              FROM booking_equipment be 
              JOIN booking b ON be.id_booking = b.id_booking 
              WHERE be.id_equipment = $id_equipment 
              AND b.status IN ('approved', 'borrowed') 
              AND b.start_date <= '$today' 
              AND b.end_date >= '$today'";
    
    $result = mysqli_query($connect, $query);
    $borrowedQty = 0;
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $borrowedQty = (int)$row['total_borrowed'];
    }
    
    return $borrowedQty;
}

// Validate equipment ID
if (!isset($_GET['id_equipment']) || !is_numeric($_GET['id_equipment'])) {
    echo "<script>alert('No equipment selected to update.'); window.location.href='admin_view_equipment.php';</script>";
    exit;
}

$id_equipment = (int)$_GET['id_equipment'];
$query = "SELECT * FROM equipment WHERE id_equipment = $id_equipment";
$result = mysqli_query($connect, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "<script>alert('Equipment not found.'); window.location.href='admin_view_equipment.php';</script>";
    exit;
}

$equipment = mysqli_fetch_assoc($result);
$borrowedQty = calculateBorrowedQty($connect, $id_equipment);
$minAllowedQty = $borrowedQty; // Minimum cannot be less than currently borrowed items

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_name = mysqli_real_escape_string($connect, $_POST['name']);
    $category = mysqli_real_escape_string($connect, $_POST['category']);
    $qty = (int)$_POST['qty'];
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    $model = isset($_POST['model']) && $_POST['model'] !== '' ? mysqli_real_escape_string($connect, $_POST['model']) : null;
    $remove_picture = isset($_POST['remove_picture']);

    $picture = $equipment['picture'];

    // Enforce quantity validation
    if ($qty < $minAllowedQty || $qty < 0) {
        echo "<script>alert('Quantity must be at least $minAllowedQty (currently borrowed items) and not negative.');</script>";
    } else {
        // Handle picture removal
        if ($remove_picture && $picture && file_exists($picture)) {
            unlink($picture);
            $picture = null;
        }

        // Handle picture upload
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_name = basename($_FILES["picture"]["name"]);
            $target_file = $target_dir . time() . "_" . $file_name;

            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
                if ($picture && file_exists($picture)) {
                    unlink($picture);
                }
                $picture = $target_file;
            }
        }

        // Update query
        $update_query = "UPDATE equipment SET 
                        name = '$new_name',
                        category = '$category',
                        qty = $qty,
                        model = " . ($model ? "'$model'" : "NULL") . ",
                        status = '$status',
                        picture = " . ($picture ? "'$picture'" : "NULL") . "
                    WHERE id_equipment = $id_equipment";

        if (mysqli_query($connect, $update_query)) {
            header("Location: admin_view_equipment.php");
            exit;
        } else {
            echo "Error updating equipment: " . mysqli_error($connect);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Equipment - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script>
        function validateForm() {
            const name = document.forms["equipmentForm"]["name"].value.trim();
            const category = document.forms["equipmentForm"]["category"].value;
            const qty = parseInt(document.forms["equipmentForm"]["qty"].value);
            const minAllowedQty = <?= $minAllowedQty ?>;
            const status = document.forms["equipmentForm"]["status"].value;

            if (name === "" || category === "" || isNaN(qty) || status === "") {
                alert("Please fill in all required fields.");
                return false;
            }

            if (qty < 0) {
                alert("Quantity cannot be less than 0.");
                return false;
            }

            if (qty < minAllowedQty) {
                alert(`Quantity cannot be less than currently borrowed items (${minAllowedQty}).`);
                return false;
            }

            return true;
        }
    </script>
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
                        <span class="breadcrumb-item active">Update Equipment</span>
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
                <!-- Update Equipment Form -->
                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fas fa-edit" style="margin-right: 8px; color: var(--primary-color);"></i>
                            Update Equipment #<?= $equipment['id_equipment'] ?>
                        </h2>
                        <div class="table-meta">
                            <i class="fas fa-info-circle"></i>
                            Modify equipment details below
                        </div>
                    </div>

                    <div style="padding: 32px;">
                        <form name="equipmentForm" method="post" enctype="multipart/form-data" onsubmit="return validateForm();" style="max-width: 600px;">
                            <div style="display: grid; gap: 24px;">
                                <!-- Equipment Name -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-tag" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Equipment Name *
                                    </label>
                                    <input type="text" name="name" class="form-input" required 
                                           value="<?= htmlspecialchars($equipment['name']) ?>"
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
                                            'Stage Equipment',
                                            'Audio Equipment',
                                            'Visual Equipment',
                                            'Lighting Equipment',
                                            'Furniture & Seating',
                                            'Tents & Canopies',
                                            'Decor & Draping',
                                            'Power & Electrical',
                                            'Staging & Structures',
                                            'Signage & Display',
                                            'Catering Equipment',
                                            'Climate Control',
                                            'Event Technology',
                                            'Sanitation & Safety',
                                            'Transportation & Storage'
                                        ];
                                        foreach ($categories as $cat) {
                                            $selected = ($equipment['category'] == $cat) ? 'selected' : '';
                                            echo "<option value='$cat' $selected>$cat</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calculator" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Total Quantity *
                                        <span style="color: var(--text-muted); font-weight: 400; font-size: 12px;">
                                            (Currently borrowed: <?= $borrowedQty ?> | Total: <?= $equipment['qty'] ?>)
                                        </span>
                                    </label>
                                    <input type="number" name="qty" class="form-input" required 
                                           min="<?= $minAllowedQty ?>"
                                           value="<?= $equipment['qty'] ?>"
                                           placeholder="Enter total quantity" 
                                           style="padding: 12px 16px; font-size: 14px;">
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Must maintain at least <?= $minAllowedQty ?> (<?= $borrowedQty ?> currently borrowed)
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-toggle-on" style="margin-right: 6px; color: var(--primary-color);"></i>
                                        Status *
                                    </label>
                                    <select name="status" class="form-select" required 
                                            style="padding: 12px 16px; font-size: 14px;">
                                        <option value="">-- Select Status --</option>
                                        <option value="Available" <?= $equipment['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                                        <option value="Maintenance" <?= $equipment['status'] === 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                    </select>
                                </div>

                                <!-- Model -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-barcode" style="margin-right: 6px; color: var(--text-muted);"></i>
                                        Model <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span>
                                    </label>
                                    <input type="text" name="model" class="form-input" 
                                           value="<?= htmlspecialchars($equipment['model']) ?>"
                                           placeholder="Enter model number or description" 
                                           style="padding: 12px 16px; font-size: 14px;">
                                </div>

                                <!-- Current Picture -->
                                <?php if (!empty($equipment['picture']) && file_exists($equipment['picture'])) { ?>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-image" style="margin-right: 6px; color: var(--text-muted);"></i>
                                        Current Picture
                                    </label>
                                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; background-color: var(--bg-primary);">
                                        <img src="<?= $equipment['picture'] ?>" alt="Current Equipment Image" 
                                             style="max-width: 200px; max-height: 200px; border-radius: var(--radius-md); margin-bottom: 12px; display: block;">
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--error-color);">
                                            <input type="checkbox" name="remove_picture" style="margin: 0;">
                                            <i class="fas fa-trash"></i>
                                            Remove Current Image
                                        </label>
                                    </div>
                                </div>
                                <?php } else { ?>
                                <div class="form-group">
                                    <div style="padding: 16px; background-color: var(--bg-primary); border-radius: var(--radius-md); text-align: center; color: var(--text-muted);">
                                        <i class="fas fa-image" style="font-size: 24px; margin-bottom: 8px; opacity: 0.5;"></i>
                                        <div>No image currently uploaded</div>
                                    </div>
                                </div>
                                <?php } ?>

                                <!-- Upload New Picture -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-upload" style="margin-right: 6px; color: var(--text-muted);"></i>
                                        Upload New Picture <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span>
                                    </label>
                                    <div style="border: 2px dashed var(--border-color); border-radius: var(--radius-md); padding: 24px; text-align: center; background-color: var(--bg-primary);">
                                        <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: var(--text-muted); margin-bottom: 12px;"></i>
                                        <div style="margin-bottom: 12px;">
                                            <input type="file" name="picture" accept="image/*" 
                                                   style="display: none;" id="picture-upload">
                                            <label for="picture-upload" class="btn btn-secondary" style="cursor: pointer;">
                                                <i class="fas fa-folder-open"></i>
                                                Choose New Image File
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
                                        Update Equipment
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