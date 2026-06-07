<?php
session_start();
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include("db.php");

// Get staff name from session
$staff_name = $_SESSION['staff_name'] ?? "Admin User";

$club_name = isset($_GET['club_name']) ? $_GET['club_name'] : '';
$error_message = "";
$success_message = "";
$club_data = null;

// Fetch club data
if (!empty($club_name)) {
    $stmt = $connect->prepare("SELECT * FROM club WHERE club_name = ?");
    $stmt->bind_param("s", $club_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $club_data = $result->fetch_assoc();
    $stmt->close();

    if (!$club_data) {
        $error_message = "Club not found!";
    }
} else {
    $error_message = "No club specified!";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_name_input = mysqli_real_escape_string($connect, $_POST['club_name']);
    $type = mysqli_real_escape_string($connect, $_POST['type']);
    $adv_name = mysqli_real_escape_string($connect, $_POST['adv_name']);
    $adv_tel = mysqli_real_escape_string($connect, $_POST['adv_tel']);
    $adv_email = mysqli_real_escape_string($connect, $_POST['adv_email']);
    $adv_num = mysqli_real_escape_string($connect, $_POST['adv_num']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    // Validate required fields
    if (empty($club_name_input) || empty($type) || empty($adv_name) || empty($adv_tel) || empty($adv_email) || empty($adv_num) || empty($status)) {
        $error_message = "Please fill in all fields.";
    } else {
        // Check if club name changed and if new name already exists
        if ($club_name_input !== $club_name) {
            $stmt = $connect->prepare("SELECT * FROM club WHERE club_name = ?");
            $stmt->bind_param("s", $club_name_input);
            $stmt->execute();
            $check = $stmt->get_result();
            $stmt->close();
            
            if ($check->num_rows > 0) {
                $error_message = "Club name already exists. Please choose a different name.";
            }
        }

        if (empty($error_message)) {
            // Update club
            $update_stmt = $connect->prepare("UPDATE club SET club_name = ?, type = ?, adv_name = ?, adv_tel = ?, adv_email = ?, adv_num = ?, status = ? WHERE club_name = ?");
            $update_stmt->bind_param("ssssssss", $club_name_input, $type, $adv_name, $adv_tel, $adv_email, $adv_num, $status, $club_name);
            
            if ($update_stmt->execute()) {
                $success_message = "Club updated successfully!";
                $club_name = $club_name_input;
                $club_data['club_name'] = $club_name_input;
                $club_data['type'] = $type;
                $club_data['adv_name'] = $adv_name;
                $club_data['adv_tel'] = $adv_tel;
                $club_data['adv_email'] = $adv_email;
                $club_data['adv_num'] = $adv_num;
                $club_data['status'] = $status;
            } else {
                $error_message = "Failed to update club. Please try again.";
            }
            $update_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Club - UniEquip</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            min-height: 100vh;
            color: var(--text-primary);
        }

        .page-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content-full {
            flex: 1;
            padding: 0;
        }

        /* Top Header */
        .top-header {
            background: white;
            padding: 24px 32px;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            flex: 1;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .breadcrumb i {
            color: var(--primary-color);
            font-size: 16px;
        }

        .breadcrumb-separator {
            color: var(--border-color);
            margin: 0 4px;
        }

        .breadcrumb-item {
            color: var(--text-primary);
            font-weight: 500;
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb a:hover {
            color: #4f46e5;
        }

        /* Content Area */
        .content-area {
            padding: 40px 32px;
            flex: 1;
        }

        /* Edit Container */
        .edit-container {
            max-width: 700px;
            margin: 0 auto;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .edit-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 40px;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
        }

        .edit-card:hover {
            box-shadow: var(--shadow-xl);
            border-color: #cbd5e1;
        }

        /* Header Section */
        .edit-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 2px solid var(--bg-secondary);
        }

        .edit-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), #4338ca);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
            box-shadow: var(--shadow-md);
            animation: bounceIn 0.6s ease;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .edit-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .edit-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-lg);
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert i {
            font-size: 18px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .alert-success {
            background: linear-gradient(135deg, rgb(16 185 129 / 0.1), rgb(5 150 105 / 0.05));
            border: 1.5px solid rgb(16 185 129 / 0.3);
            color: #059669;
            border-radius: var(--radius-lg);
        }

        .alert-error {
            background: linear-gradient(135deg, rgb(239 68 68 / 0.1), rgb(220 38 38 / 0.05));
            border: 1.5px solid rgb(239 68 68 / 0.3);
            color: #dc2626;
            border-radius: var(--radius-lg);
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 28px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .form-label i {
            margin-right: 6px;
            color: var(--primary-color);
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            font-family: inherit;
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .form-input:hover,
        .form-select:hover {
            border-color: #bfdbfe;
            background-color: var(--bg-primary);
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: var(--bg-primary);
            box-shadow: 0 0 0 4px rgb(99 102 241 / 0.1), 0 0 0 8px rgb(99 102 241 / 0.05);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 40px;
            padding-top: 24px;
            border-top: 2px solid var(--bg-secondary);
        }

        .btn {
            padding: 12px 28px;
            border-radius: var(--radius-md);
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #4338ca);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgb(99 102 241 / 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border: 2px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: var(--bg-primary);
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .btn i {
            font-size: 16px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content-area {
                padding: 24px 16px;
            }

            .top-header {
                padding: 16px 20px;
                flex-direction: column;
                gap: 12px;
            }

            .edit-card {
                padding: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .edit-title {
                font-size: 24px;
            }

            .edit-icon {
                width: 64px;
                height: 64px;
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .edit-container {
                margin: 0;
            }

            .edit-card {
                padding: 20px;
                border-radius: var(--radius-lg);
            }

            .edit-header {
                margin-bottom: 24px;
                padding-bottom: 16px;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-actions {
                margin-top: 24px;
                padding-top: 16px;
            }

            .breadcrumb {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Main Content -->
        <div class="main-content-full">
            <!-- Top Header -->
            <div class="top-header">
                <div class="header-left">
                    <div class="breadcrumb">
                        <i class="fas fa-home"></i>
                        <span class="breadcrumb-separator">/</span>
                        <a href="view_club.php" style="color: var(--text-secondary); text-decoration: none;">Clubs</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Edit Club</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <div class="edit-container">
                    <div class="edit-card">
                        <div class="edit-header">
                            <div class="edit-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <h2 class="edit-title">Edit Club</h2>
                            <p class="edit-subtitle">Update club information</p>
                        </div>

                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= htmlspecialchars($error_message) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <?= htmlspecialchars($success_message) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($club_data): ?>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-tag"></i>Club Name</label>
                                    <input type="text" name="club_name" class="form-input" placeholder="Enter club name" value="<?= htmlspecialchars($club_data['club_name']) ?>" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-layer-group"></i>Type</label>
                                        <select name="type" class="form-select" required>
                                            <option value="">-- Select Type --</option>
                                            <option value="Open" <?= $club_data['type'] === 'Open' ? 'selected' : '' ?>>Open</option>
                                            <option value="Close" <?= $club_data['type'] === 'Close' ? 'selected' : '' ?>>Close</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-check-square"></i>Status</label>
                                        <select name="status" class="form-select" required>
                                            <option value="">-- Select Status --</option>
                                            <option value="active" <?= $club_data['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= $club_data['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-user-tie"></i>Advisor Name</label>
                                    <input type="text" name="adv_name" class="form-input" placeholder="Enter advisor name" value="<?= htmlspecialchars($club_data['adv_name']) ?>" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-phone"></i>Advisor Phone</label>
                                        <input type="tel" name="adv_tel" class="form-input" placeholder="Enter phone number" value="<?= htmlspecialchars($club_data['adv_tel']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-envelope"></i>Advisor Email</label>
                                        <input type="email" name="adv_email" class="form-input" placeholder="Enter email address" value="<?= htmlspecialchars($club_data['adv_email']) ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-id-card"></i>Advisor Staff Number</label>
                                    <input type="text" name="adv_num" class="form-input" placeholder="Enter staff number" value="<?= htmlspecialchars($club_data['adv_num']) ?>" required>
                                </div>

                                <div class="form-actions">
                                    <a href="view_club.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= htmlspecialchars($error_message ?: "Club not found") ?>
                            </div>
                            <div class="form-actions" style="margin-top: 24px;">
                                <a href="view_club.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    Back to Clubs
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
