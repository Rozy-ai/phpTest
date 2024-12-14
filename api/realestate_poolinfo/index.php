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

// Получение id_pool из запроса
$id_pool = $_POST['id_pool'] ?? 0;

if (!$id_pool) {
    ToDie($MySQLi, 'Invalid id_pool');
}

// Получаем информацию о пуле
$stmt = $MySQLi->prepare("SELECT sum_goal, sum, status FROM estatepool WHERE id = ?");
$stmt->bind_param('i', $id_pool);
$stmt->execute();
$result = $stmt->get_result();

// Проверка, если пул не найден
if ($result->num_rows === 0) {
    ToDie($MySQLi, 'Pool not found');
}

// Получаем данные по пулу
$poolData = $result->fetch_assoc();

// Получаем список подарков для этого пула
$stmt_gifts = $MySQLi->prepare("SELECT id, name, general, date_close FROM estatepool_gifts WHERE id_pool = ?");
$stmt_gifts->bind_param('i', $id_pool);
$stmt_gifts->execute();
$result_gifts = $stmt_gifts->get_result();

// Формируем массив для подарков
$gifts = [];
while ($gift = $result_gifts->fetch_assoc()) {
    // Получаем информацию о победителе для каждого подарка
    $stmt_winner = $MySQLi->prepare("SELECT id_user, ticket FROM estatepool_usertickets WHERE id_gift = ? AND win = 1 LIMIT 1");
    $stmt_winner->bind_param('i', $gift['id']);
    $stmt_winner->execute();
    $result_winner = $stmt_winner->get_result();
    $winner = $result_winner->fetch_assoc();

    // Добавляем информацию о подарке в массив
    $gifts[] = [
        'id' => $gift['id'],
        'name' => $gift['name'],
        'point' => 0, // Здесь можно вычислить точку для промежуточной цели
        'sum' => ($gift['general'] == 1) ? $poolData['sum_goal'] : ($poolData['sum_goal'] / 6), // Примерное вычисление суммы
        'date_close' => $gift['date_close'] ? $gift['date_close'] : null,
        'id_winner' => $winner ? $winner['id_user'] : 0,
        'general' => $gift['general']
    ];

    $stmt_winner->close();
}

$stmt_gifts->close();

// Получаем список победителей
$winners = [];
foreach ($gifts as $index => $gift) {
    if ($gift['date_close']) {
        $winners[] = [
            'num' => $index + 1,
            'id_gift' => $gift['id'],
            'id_user' => $gift['id_winner'],
            'ticket' => $gift['id_winner'] ? $winner['ticket'] : null,
            'name' => $gift['name'],
            'date_close' => $gift['date_close']
        ];
    }
}

// Формируем ответ
$response = [
    'success' => true,
    'id_pool' => $id_pool,
    'sum_goal' => $poolData['sum_goal'],
    'sum' => $poolData['sum'],
    'status' => $poolData['status'],
    'winners' => $winners,
    'gifts' => $gifts
];

// Закрытие соединения
$MySQLi->close();

// Выводим результат в JSON
echo json_encode($response);

?>