<?php
// api/get_stats.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

// Total late returns
$sql1 = "
    SELECT COUNT(*) as total
    FROM booking b
    WHERE b.end_date < CURDATE() 
    AND b.status IN ('borrowed', 'overdue')
";
$result1 = mysqli_query($connect, $sql1);
$totalLateReturns = mysqli_fetch_assoc($result1)['total'] ?? 0;

// Worst offender equipment
$sql2 = "
    SELECT e.name
    FROM equipment e
    LEFT JOIN booking_equipment be ON e.id_equipment = be.id_equipment
    LEFT JOIN booking b ON be.id_booking = b.id_booking
    WHERE b.end_date < CURDATE() 
    AND b.status IN ('borrowed', 'overdue')
    GROUP BY e.id_equipment, e.name
    ORDER BY COUNT(*) DESC
    LIMIT 1
";
$result2 = mysqli_query($connect, $sql2);
$worstOffender = mysqli_fetch_assoc($result2)['name'] ?? 'None';

// Current month late returns
$sql3 = "
    SELECT COUNT(*) as current_month
    FROM booking b
    WHERE b.end_date < CURDATE() 
    AND b.status IN ('borrowed', 'overdue')
    AND DATE_FORMAT(b.end_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
";
$result3 = mysqli_query($connect, $sql3);
$currentMonth = mysqli_fetch_assoc($result3)['current_month'] ?? 0;

// Previous month for trend calculation
$sql4 = "
    SELECT COUNT(*) as previous_month
    FROM booking b
    WHERE b.end_date < CURDATE() 
    AND b.status IN ('borrowed', 'overdue')
    AND DATE_FORMAT(b.end_date, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')
";
$result4 = mysqli_query($connect, $sql4);
$previousMonth = mysqli_fetch_assoc($result4)['previous_month'] ?? 0;

// Trend
$trendIndicator = 'No Change';
if ($previousMonth > 0) {
    $change = (($currentMonth - $previousMonth) / $previousMonth) * 100;
    if ($change > 0) {
        $trendIndicator = '+' . round($change, 1) . '%';
    } elseif ($change < 0) {
        $trendIndicator = round($change, 1) . '%';
    }
} elseif ($currentMonth > 0) {
    $trendIndicator = '+100%';
}

echo json_encode([
    'total_late_returns' => (int)$totalLateReturns,
    'worst_offender' => $worstOffender,
    'current_month' => date('F Y'),
    'trend_indicator' => $trendIndicator
]);
?>
