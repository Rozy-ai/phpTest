<?php 

include '../../config.php';

$MySQLi = new mysqli('localhost',$DB['username'],$DB['password'],$DB['dbname']);
$MySQLi->query("SET NAMES 'utf8'");
$MySQLi->set_charset('utf8mb4');
if ($MySQLi->connect_error) die;
function ToDie($MySQLi, $message = 'Unexpected error') {
    $MySQLi->close();
    echo json_encode(['error' => $message]);
    die;
}

$sum_goal = $_POST['sum_goal'] ?? 0;
if (!$sum_goal || $sum_goal <= 0) {
    echo json_encode(['error' => 'Invalid sum_goal']);
    return;
}

$stmt = $MySQLi->prepare("INSERT INTO estatepool (date_start, date_close, sum, sum_goal, status) VALUES (NOW(), NOW(), 0, ?, 0)");
if (!$stmt) {
    ToDie($MySQLi, 'Failed to prepare statement: ' . $MySQLi->error);
}

$stmt->bind_param('d', $sum_goal);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $MySQLi->insert_id]);
} else {
    ToDie($MySQLi, 'Failed to execute statement: ' . $stmt->error);
}

$stmt->close();
$MySQLi->close();

