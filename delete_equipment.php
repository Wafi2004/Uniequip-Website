<?php
session_start();

// Only allow admin
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include("db.php");

// Check if 'id_equipment' is provided and valid
if (!isset($_GET['id_equipment']) || !is_numeric($_GET['id_equipment'])) {
    echo "<script>alert('Invalid equipment ID.'); window.location.href='admin_view_equipment.php';</script>";
    exit;
}

$id_equipment = (int)$_GET['id_equipment'];

// Check if equipment exists and get its details
$check_query = "SELECT * FROM equipment WHERE id_equipment = ?";
$stmt = mysqli_prepare($connect, $check_query);
mysqli_stmt_bind_param($stmt, "i", $id_equipment);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>alert('Equipment not found.'); window.location.href='admin_view_equipment.php';</script>";
    exit;
}

$equipment = mysqli_fetch_assoc($result);
$picturePath = $equipment['picture'];

// Check if equipment is currently being used in any active bookings
$booking_check_query = "SELECT COUNT(*) as active_bookings 
                       FROM booking_equipment be 
                       JOIN booking b ON be.id_booking = b.id_booking 
                       WHERE be.id_equipment = ? 
                       AND b.status IN ('pending', 'approved', 'borrowed')";

$stmt2 = mysqli_prepare($connect, $booking_check_query);
mysqli_stmt_bind_param($stmt2, "i", $id_equipment);
mysqli_stmt_execute($stmt2);
$booking_result = mysqli_stmt_get_result($stmt2);
$booking_row = mysqli_fetch_assoc($booking_result);

if ($booking_row['active_bookings'] > 0) {
    echo "<script>
        alert('Cannot delete this equipment because it is currently being used in active bookings.\\n\\nPlease wait for all bookings to be completed or returned before deleting this equipment.');
        window.location.href='admin_view_equipment.php';
    </script>";
    exit;
}

// Start transaction for safe deletion
mysqli_autocommit($connect, false);

try {
    // First, delete any completed booking equipment records
    $delete_booking_equipment = "DELETE FROM booking_equipment 
                                WHERE id_equipment = ? 
                                AND id_booking IN (
                                    SELECT id_booking FROM booking 
                                    WHERE status IN ('returned', 'cancelled', 'rejected')
                                )";
    $stmt3 = mysqli_prepare($connect, $delete_booking_equipment);
    mysqli_stmt_bind_param($stmt3, "i", $id_equipment);
    mysqli_stmt_execute($stmt3);

    // Now delete the equipment
    $delete_equipment_query = "DELETE FROM equipment WHERE id_equipment = ?";
    $stmt4 = mysqli_prepare($connect, $delete_equipment_query);
    mysqli_stmt_bind_param($stmt4, "i", $id_equipment);
    
    if (mysqli_stmt_execute($stmt4)) {
        // Commit the transaction
        mysqli_commit($connect);
        
        // Delete image file if exists
        if ($picturePath && !empty($picturePath) && file_exists($picturePath)) {
            unlink($picturePath);
        }

        echo "<script>
            alert('Equipment deleted successfully!');
            window.location.href='admin_view_equipment.php';
        </script>";
    } else {
        // Rollback on failure
        mysqli_rollback($connect);
        throw new Exception(mysqli_error($connect));
    }

} catch (Exception $e) {
    // Rollback transaction on any error
    mysqli_rollback($connect);
    
    $error_message = $e->getMessage();
    
    // Check for foreign key constraint errors
    if (strpos($error_message, 'foreign key constraint') !== false || 
        strpos($error_message, 'Cannot delete') !== false) {
        echo "<script>
            alert('Cannot delete this equipment because it is referenced in booking records.\\n\\nTo delete this equipment, you must first remove all associated booking records.');
            window.location.href='admin_view_equipment.php';
        </script>";
    } else {
        echo "<script>
            alert('Failed to delete equipment: " . addslashes($error_message) . "');
            window.location.href='admin_view_equipment.php';
        </script>";
    }
}

// Restore autocommit
mysqli_autocommit($connect, true);
mysqli_close($connect);
?>
