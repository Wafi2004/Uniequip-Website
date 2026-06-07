<?php
session_start();
include("db.php");

// Redirect if not admin
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Base query
$query = "
    SELECT 
        b.id_booking, b.event_name, b.status, b.start_date, b.end_date, b.club_name, b.stud_num,
        u.stud_name
    FROM booking b
    JOIN user u ON b.stud_num = u.stud_num
";

// Filter by status
if (!empty($filter_status)) {
    $query .= " WHERE b.status = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("s", $filter_status);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query .= " ORDER BY b.status = 'pending' DESC, b.start_date ASC";
    $result = $connect->query($query);
}

$statuses = ["pending", "approved", "rejected", "borrowed", "late", "not claimed"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Approve Booking Requests</title>
</head>
<body>
    <h2>Booking Approval Table (Admin View)</h2>

    <!-- Filter Form -->
    <form method="get" action="admin_approve_booking.php">
        <label for="status">Filter by Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="">-- Show All --</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?php echo $status; ?>" <?php if ($filter_status === $status) echo 'selected'; ?>>
                    <?php echo ucfirst($status); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <br>

    <?php if ($result->num_rows > 0): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>Booking ID</th>
                <th>Event Name</th>
                <th>Student</th>
                <th>Student ID</th>
                <th>Club</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Details</th>
                <th>Current Status</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id_booking']); ?></td>
                    <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['stud_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['stud_num']); ?></td>
                    <td><?php echo htmlspecialchars($row['club_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                    <td><a href="booking_detail.php?id=<?php echo $row['id_booking']; ?>">View Details</a></td>
                    <td><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
                    <td>
                        <form method="post" action="admin_update_booking.php" style="display:inline;">
                            <input type="hidden" name="id_booking" value="<?php echo $row['id_booking']; ?>">
                            <select name="new_status">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php if ($row['status'] === $status) echo 'selected'; ?>>
                                        <?php echo ucfirst($status); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="submit" value="Update">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No bookings found<?php if ($filter_status) echo " for status: " . htmlspecialchars($filter_status); ?>.</p>
    <?php endif; ?>
    <br><br><br>
    <input type="button" value="Back" onclick="window.location.href='admin_dashboard.php'">


</body>
</html>
