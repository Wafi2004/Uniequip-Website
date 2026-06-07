<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once 'db.php';

$sql = "
    SELECT 
        e.id_equipment,
        e.name,
        e.category,
        COUNT(CASE 
            WHEN b.end_date < CURDATE() 
            AND b.status IN ('borrowed', 'overdue') 
            THEN 1 
        END) as late_returns,
        COUNT(CASE 
            WHEN b.end_date < DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            AND b.end_date >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
            AND b.status IN ('borrowed', 'overdue')
            THEN 1 
        END) as last_month_returns
    FROM equipment e
    LEFT JOIN booking_equipment be ON e.id_equipment = be.id_equipment
    LEFT JOIN booking b ON be.id_booking = b.id_booking
    GROUP BY e.id_equipment, e.name, e.category
    HAVING late_returns > 0
    ORDER BY late_returns DESC
";

$result = mysqli_query($connect, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . mysqli_error($connect)]);
    exit;
}

$results = [];
while ($row = mysqli_fetch_assoc($result)) {
    $current = (int)$row['late_returns'];
    $previous = (int)$row['last_month_returns'];
    $trend = $previous == 0 ? ($current > 0 ? 1 : 0) : ($current - $previous);
    $row['trend'] = $trend;
    $results[] = $row;
}

echo json_encode($results);
?>
