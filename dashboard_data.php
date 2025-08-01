<?php
header('Content-Type: application/json');
include("../config.php");

$username=isset($_GET['username']) ? trim($_GET['username']) : null;
$status=isset($_GET['status']) ? trim($_GET['status']) : null;
$from=isset($_GET['from']) ? trim($_GET['from']) : null;
$to=isset($_GET['to']) ? trim($_GET['to']) : null;

$where=[];
$params=[];
$types = '';

if ($username) {
    $where[] = "username = ?";
    $params[] = $username;
    $types .= 's';
}
if ($status && in_array($status, ['success', 'fail'])) {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($from && strtotime($from)) {
    $where[] = "created_at >= ?";
    $params[] = $from;
    $types .= 's';
}
if ($to && strtotime($to)) {
    $where[] = "created_at <= ?";
    $params[] = $to;
    $types .= 's';
}
$whereSQL=$where ? "WHERE " . implode(" AND ", $where) : "";

// Success vs fail
$success_count = 0;
$fail_count = 0;
$sql = "SELECT status, COUNT(*) AS count FROM login_attempts $whereSQL GROUP BY status";
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        if($row['status'] == 'success'){
            $success_count = (int)$row['count'];
        } else {
            $fail_count += (int)$row['count'];
        }
    }
    $stmt->close();
}

// Top 5 usernames
$top_usernames = [];
$top_where = $where;
$top_where[] = "username != ''";
$top_whereSQL = "WHERE " . implode(" AND ", $top_where);
$sql_top = "SELECT username, COUNT(*) AS count FROM login_attempts $top_whereSQL GROUP BY username ORDER BY count DESC LIMIT 5";
$stmt = $conn->prepare($sql_top);
if ($stmt) {
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $top_usernames[] = ['username' => $row['username'], 'count' => (int)$row['count']];
    }
    $stmt->close();
}

// Attempts over time
$attempts_over_time = [];
// --- 4. Corrected SQL syntax for attempts over time. This query now correctly combines the dynamic filters with the 24-hour window.
$time_where = $where;
$time_where[] = "created_at >= NOW() - INTERVAL 24 HOUR";
$time_whereSQL = "WHERE " . implode(" AND ", $time_where);
$sql_time = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour_slot, COUNT(*) as count FROM login_attempts $time_whereSQL GROUP BY hour_slot ORDER BY hour_slot ASC";
$stmt = $conn->prepare($sql_time);
if ($stmt) {
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $attempts_over_time[] = [
            'time' => $row['hour_slot'],
            'count' => (int)$row['count']
        ];
    }
    $stmt->close();
}
$conn->close();

echo json_encode([
    'success_count' => $success_count,
    'fail_count' => $fail_count,
    'top_usernames' => $top_usernames,
    'attempts_over_time' => $attempts_over_time
]);
?>
