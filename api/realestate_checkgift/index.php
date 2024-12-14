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

// Получение данных пула
$stmt = $MySQLi->prepare("SELECT sum, sum_goal FROM estatepool WHERE id = ?");
$stmt->bind_param('i', $id_pool);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    ToDie($MySQLi, 'Pool not found');
}

$pool = $result->fetch_assoc();
$current_sum = (float)$pool['sum'];
$goal_sum = (float)$pool['sum_goal'];
$stmt->close();

// Получение списка подарков для пула
$stmt = $MySQLi->prepare("SELECT id, name, general, priority, id_winner FROM estatepool_gifts WHERE id_pool = ? ORDER BY general DESC, priority ASC");
$stmt->bind_param('i', $id_pool);
$stmt->execute();
$gifts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Проверка целей и розыгрыш
$checkpoints = [];
$general_gift_id = null;
foreach ($gifts as $gift) {
    if ($gift['general'] == 1) {
        $general_gift_id = $gift['id'];
    } else {
        $checkpoints[] = $goal_sum / count($gifts); // Промежуточные цели
    }
}

$results = [];
foreach ($gifts as $gift) {
    if ($gift['id_winner']) {
        continue; // Пропускаем уже разыгранные подарки
    }

    // Проверяем, достигнута ли цель для подарка
    $target_sum = $gift['general'] ? $goal_sum : $checkpoints[array_search($gift['id'], array_column($gifts, 'id'))];
    if ($current_sum >= $target_sum) {
        // Определяем победителя
        $winner = null;
        $stmt = $MySQLi->prepare("SELECT id, id_user FROM estatepool_usertickets WHERE id_pool = ? AND win = 0 ORDER BY RAND() LIMIT 1");
        $stmt->bind_param('i', $id_pool);
        $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$ticket) {
            // Если билетов нет, создаем новый для победителя
            $stmt = $MySQLi->prepare("INSERT INTO estatepool_usertickets (ticket, id_ticket, id_user, id_pool, id_gift, win) VALUES (?, NULL, NULL, ?, ?, 1)");
            $new_ticket = strtoupper(substr(md5(uniqid()), 0, 8)); // Генерация случайного кода билета
            $stmt->bind_param('sii', $new_ticket, $id_pool, $gift['id']);
            $stmt->execute();
            $stmt->close();

            $winner = [
                'ticket' => $new_ticket,
                'id_user' => null,
            ];
        } else {
            // Победитель по существующему билету
            $stmt = $MySQLi->prepare("UPDATE estatepool_usertickets SET win = 1, id_gift = ? WHERE id = ?");
            $stmt->bind_param('ii', $gift['id'], $ticket['id']);
            $stmt->execute();
            $stmt->close();

            $winner = $ticket;
        }

        // Обновляем данные подарка
        $stmt = $MySQLi->prepare("UPDATE estatepool_gifts SET date_close = ?, id_winner = ? WHERE id = ?");
        $time = time();
        $stmt->bind_param('iii', $time, $winner['id_user'], $gift['id']);
        $stmt->execute();
        $stmt->close();

        // Добавляем результат в массив
        $results[] = [
            'gift_id' => $gift['id'],
            'gift_name' => $gift['name'],
            'winner_ticket' => $winner['ticket'] ?? null,
            'winner_user' => $winner['id_user'] ?? null,
            'date_close' => $time,
        ];
    }
}

// Вывод результата
echo json_encode(['success' => true, 'results' => $results]);
$MySQLi->close();