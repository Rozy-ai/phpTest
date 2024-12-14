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

$id_pool = $_POST['id_pool'] ?? null;
$name = $_POST['name'] ?? null;
$general = $_POST['general'] ?? 0;

if (!$id_pool || !$name) {
    echo json_encode(['error' => 'Missing id_pool or name']);
    return;
}

$stmt = $MySQLi->prepare("SELECT id FROM estatepool WHERE id = ?");
$stmt->bind_param('i', $id_pool);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['error' => 'Pool not found']);
    return;
}
$stmt->close();

// Проверка на наличие другого главного подарка
if ($general == 1) {
    $stmt = $MySQLi->prepare("SELECT id FROM estatepool_gifts WHERE id_pool = ? AND general = 1");
    $stmt->bind_param('i', $id_pool);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['error' => 'A main gift already exists']);
        return;
    }
    $stmt->close();
}

// Добавление подарка
$stmt = $MySQLi->prepare("INSERT INTO estatepool_gifts (id_pool, name, date_close, id_winner, id_not_winner, priority, general) 
                      VALUES (?, ?, NOW(), NULL, NULL, 0, ?)");
$stmt->bind_param('isi', $id_pool, $name, $general);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $MySQLi->insert_id]);
} else {
    echo json_encode(['error' => 'Failed to add gift']);
}

$stmt->close();
$MySQLi->close();

