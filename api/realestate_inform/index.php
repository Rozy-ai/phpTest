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

// Получение id_pool
$id_pool = $_POST['id_pool'] ?? 0;
if (!$id_pool || $id_pool <= 0) {
    ToDie($MySQLi, 'Invalid id_pool');
}

// Получаем данные пула из таблицы estatepool
$stmt = $MySQLi->prepare("SELECT id, status, sum_goal, sum FROM estatepool WHERE id = ?");
$stmt->bind_param('i', $id_pool);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    ToDie($MySQLi, 'Pool not found');
}

$pool = $result->fetch_assoc();
$stmt->close();

// Получаем подарки для пула из таблицы estatepool_gifts
$stmt = $MySQLi->prepare("SELECT id, name, date_close, id_winner, general FROM estatepool_gifts WHERE id_pool = ?");
$stmt->bind_param('i', $id_pool);
$stmt->execute();
$gifts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Динамически вычисляем суммы для промежуточных подарков
$checkpoints = [];
$general_gift_id = null;
$sum_for_checkpoints = $pool['sum_goal'];

foreach ($gifts as $gift) {
    if ($gift['general'] == 1) {
        $general_gift_id = $gift['id'];
    } else {
        // Рассчитываем промежуточную цель для каждого простого подарка
        $checkpoints[] = $sum_for_checkpoints / count($gifts);
    }
}

// Формируем массив для вывода
$response = [
    'success' => true,
    'id_pool' => $pool['id'],
    'status' => $pool['status'],
    'sum_goal' => $pool['sum_goal'],
    'sum' => $pool['sum'],
    'gifts' => []
];

foreach ($gifts as $gift) {
    $response['gifts'][] = [
        'name' => $gift['name'],
        'sum' => $gift['general'] == 1 ? $sum_for_checkpoints : ($sum_for_checkpoints / count($gifts)),
        'date_close' => $gift['date_close'] ? date('Y-m-d H:i:s', strtotime($gift['date_close'])) : null,
        'id_winner' => $gift['id_winner'] ?? 0,
        'general' => $gift['general']
    ];
}

// Выводим результат в JSON
echo json_encode($response);
$MySQLi->close();