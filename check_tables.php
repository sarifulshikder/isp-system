<?php
include 'config.php';
$tables = ['work_diary', 'diary_comments'];
foreach ($tables as $table) {
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    if ($res->num_rows == 0) {
        echo "Table $table missing.
";
    } else {
        echo "Table $table exists.
";
    }
}
?>
