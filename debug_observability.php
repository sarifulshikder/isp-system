<?php
include 'config.php';

$query = "SELECT metric_type, metric_value, recorded_at 
    FROM performance_metrics 
    WHERE target_type = 'SERVER' AND recorded_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ORDER BY recorded_at ASC";

$result = $conn->query($query);
if (!$result) {
    echo "Query Error: " . $conn->error . "
";
} else {
    echo "Query successful, row count: " . $result->num_rows . "
";
}
?>
