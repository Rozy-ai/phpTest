<?php
include '../../config.php';

$MySQLi = new mysqli('localhost', $DB['username'], $DB['password'], $DB['dbname']);
$MySQLi->query("SET NAMES 'utf8'");
$MySQLi->set_charset('utf8mb4');


if ($MySQLi->connect_error) {
    ToDie($MySQLi, 'Connection failed: ' . $MySQLi->connect_error);
}


function ToDie($MySQLi, $message = 'Unexpected error') {
    $MySQLi->close();
    echo json_encode(['error' => $message]);
    die;
}


$email = "testuser@example.com"; 
$id_ref = 0; 
$id_balance = 1; 
$sum = 100.00; 


$stmt = $MySQLi->prepare("INSERT INTO users (email, id_ref) VALUES (?, ?)");
$stmt->bind_param("si", $email, $id_ref);
$stmt->execute();

$stm_balances = $MySQLi->prepare("INSERT INTO balances (title, paysystem, currency, status, type) VALUES ('Main Balance', 'PaySystem', 'USD', 1, 1)");
$stm_balances->execute();



if ($stmt->affected_rows > 0) {
    $user_id = $stmt->insert_id; 
    echo "User created with ID: " . $user_id . "\n";

    $stmt_balance = $MySQLi->prepare("INSERT INTO users_balances (id_user, id_balance, sum, stat_sum, status, show_balance) VALUES (?, ?, ?, ?, ?, ?)");
    $stat_sum = 0.00;
    $status = 1; 
    $show_balance = 1; 

    $stmt_balance->bind_param("iiidii", $user_id, $id_balance, $sum, $stat_sum, $status, $show_balance);
    $stmt_balance->execute();

    if ($stmt_balance->affected_rows > 0) {
        echo "Balance created for user ID: " . $user_id . "\n";
    } else {
        ToDie($MySQLi, "Failed to create balance for user.");
    }
} else {
    ToDie($MySQLi, "Failed to create user.");
}

$stmt->close();
$stm_balances->close();
$stmt_balance->close();
$MySQLi->close();
?>