<?php

include '../../config.php';

$MySQLi = new mysqli('localhost', $DB['username'], $DB['password'], $DB['dbname']);
$MySQLi->query("SET NAMES 'utf8'");
$MySQLi->set_charset('utf8mb4');
if ($MySQLi->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $MySQLi->connect_error]);
    die;
}

// Функция для завершения работы
function ToDie($MySQLi, $message = 'Unexpected error') {
    $MySQLi->close();
    echo json_encode(['error' => $message]);
    die;
}

// Получение id_user и id_pool
$id_user = $_POST['id_user'] ?? 0;
$id_pool = $_POST['id_pool'] ?? 0;

if (!$id_user || !$id_pool) {
    ToDie($MySQLi, 'Invalid id_user or id_pool');
}

// Получаем список билетов для данного пользователя и пула
$stmt = $MySQLi->prepare("SELECT ticket, win FROM estatepool_usertickets WHERE id_user = ? AND id_pool = ?");
$stmt->bind_param('ii', $id_user, $id_pool);
$stmt->execute();
$result = $stmt->get_result();

// Если билетов нет
if ($result->num_rows === 0) {
    ToDie($MySQLi, 'No tickets found');
}

// Формируем ответ
$tickets = [];
while ($ticket = $result->fetch_assoc()) {
    $tickets[] = [
        'ticket' => $ticket['ticket'],
        'id_pool' => $id_pool,
        'win' => $ticket['win']
    ];
}

$stmt->close();

// Формируем ответ в формате JSON
$response = [
    'success' => true,
    'data' => $tickets
];

// Выводим результат
echo json_encode($response);

// Закрытие подключения
$MySQLi->close();