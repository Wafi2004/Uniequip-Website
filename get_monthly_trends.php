<?php
// api/get_monthly_trends.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

$range = $_GET['range'] ?? '6months';

switch ($range) {
    case 'year':
        $months = 12;
        break;
    case 'all':
        $months = 24;
        break;
    default:
        $months = 6;
}

$sql = "
    SELECT 
        DATE_FORMAT(b.end_date, '%Y-%m') as month_year,
        COUNT(CASE 
            WHEN b.end_date < CURDATE() 
            AND b.status IN ('borrowed', 'overdue') 
            THEN 1 
        END) as late_returns
    FROM booking b
    WHERE b.end_date >= DATE_SUB(CURDATE(), INTERVAL $months MONTH)
    GROUP BY DATE_FORMAT(b.end_date, '%Y-%m')
    ORDER BY month_year
";

$result = mysqli_query($connect, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . mysqli_error($connect)]);
    exit;
}

$labels = [];
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $date = DateTime::createFromFormat('Y-m', $row['month_year']);
    $labels[] = $date->format('M Y');
    $data[] = (int)$row['late_returns'];
}

echo json_encode([
    'labels' => $labels,
    'data' => $data
]);
?>
