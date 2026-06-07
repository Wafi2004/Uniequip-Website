<?php
// available.php
include 'db.php'; // This should define $conn

// SQL to calculate available quantity based on today's date
$sql = "
SELECT 
    e.id_equipment,
    e.name,
    e.category,
    e.status,
    e.qty,
    e.model,
    e.picture,
    (e.qty - IFNULL((
        SELECT SUM(be.qty)
        FROM booking_equipment be
        JOIN booking b ON be.id_booking = b.id_booking
        WHERE 
            be.id_equipment = e.id_equipment
            AND b.status = 'borrowed'
            AND CURDATE() BETWEEN b.start_date AND b.end_date
    ), 0)) AS available_today
FROM equipment e
";

$result = mysqli_query($connect, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Equipment Today</title>
</head>
<body>

<h2>Available Equipment as of <?php echo date("Y-m-d"); ?></h2>

<table border="1" cellspacing="0" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Category</th>
        <th>Status</th>
        <th>Total Qty</th>
        <th>Available Today</th>
        <th>Model</th>
        <th>Picture</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?php echo $row['id_equipment']; ?></td>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['category']; ?></td>
        <td><?php echo $row['status']; ?></td>
        <td><?php echo $row['qty']; ?></td>
        <td><?php echo $row['available_today']; ?></td>
        <td><?php echo $row['model']; ?></td>
        <td>
            <?php if (!empty($row['picture'])): ?>
                <img src="uploads/<?php echo $row['picture']; ?>" width="100">
            <?php else: ?>
                No picture
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
