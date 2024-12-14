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
$id_tickets = $_POST['id_tickets'] ?? null;
$id_user = $_POST['id_user'] ?? null;

if (!$id_pool || !$id_tickets || !$id_user) {
    echo json_encode(['error' => 'Missing required parameters']);
    return;
}

// Проверка информации о билете
$stmt = $MySQLi->prepare("SELECT sum, count FROM estatepool_tickets WHERE id = ?");
$stmt->bind_param('i', $id_tickets);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
if (!$ticket) {
    echo json_encode(['error' => 'Ticket not found']);
    return;
}
$stmt->close();

// Проверка баланса пользователя
$stmt = $MySQLi->prepare("SELECT sum FROM users_balances WHERE id_user = ? AND status = 1");
$stmt->bind_param('i', $id_user);
$stmt->execute();
$balance = $stmt->get_result()->fetch_assoc();
if (!$balance || $balance['sum'] < $ticket['sum']) {
    echo json_encode(['error' => 'Insufficient balance']);
    return;
}
$stmt->close();

// Списание средств и обновление пула
$stmt = $MySQLi->prepare("UPDATE users_balances SET sum = sum - ? WHERE id_user = ?");
$stmt->bind_param('di', $ticket['sum'], $id_user);
$stmt->execute();
$stmt->close();

$stmt = $MySQLi->prepare("UPDATE estatepool SET sum = sum + ? WHERE id = ?");
$stmt->bind_param('di', $ticket['sum'], $id_pool);
$stmt->execute();
$stmt->close();

// Генерация билетов
$stmt = $MySQLi->prepare("INSERT INTO estatepool_usertickets (ticket, id_ticket, id_user, id_pool, id_gift, win) 
                      VALUES (?, ?, ?, ?, NULL, 0)");
$ticket_code = uniqid();
for ($i = 0; $i < $ticket['count']; $i++) {
    $stmt->bind_param('siii', $ticket_code, $id_tickets, $id_user, $id_pool);
    $stmt->execute();
}

$stmt->close();
$MySQLi->close();

