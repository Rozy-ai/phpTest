<?php

include '../../config.php';

$MySQLi = new mysqli('localhost', $DB['username'], $DB['password'], $DB['dbname']);
$MySQLi->query("SET NAMES 'utf8'");
$MySQLi->set_charset('utf8mb4');
if ($MySQLi->connect_error) {
    echo json_encode(['success' => false, 'text' => 'Database connection failed: ' . $MySQLi->connect_error]);
    die;
}

// Получаем параметры из запроса
$id_user = $_POST['id_user'] ?? 0;
$id_pool = $_POST['id_pool'] ?? 0;

if (!$id_user || !$id_pool) {
    echo json_encode(['success' => false, 'text' => 'Invalid parameters']);
    die;
}

// Получаем количество всех билетов в пуле
$stmt_total_tickets = $MySQLi->prepare("SELECT COUNT(*) AS total_tickets FROM estatepool_usertickets WHERE id_pool = ?");
$stmt_total_tickets->bind_param('i', $id_pool);
$stmt_total_tickets->execute();
$result_total_tickets = $stmt_total_tickets->get_result();
$total_tickets_data = $result_total_tickets->fetch_assoc();
$total_tickets = $total_tickets_data['total_tickets'];

// Получаем информацию о билетах пользователя
$stmt_user_tickets = $MySQLi->prepare("SELECT COUNT(*) AS user_tickets, SUM(estatepool_tickets.sum) AS total_spent FROM estatepool_usertickets INNER JOIN estatepool_tickets ON estatepool_usertickets.id_ticket = estatepool_tickets.id WHERE estatepool_usertickets.id_user = ? AND estatepool_usertickets.id_pool = ?");
$stmt_user_tickets->bind_param('ii', $id_user, $id_pool);
$stmt_user_tickets->execute();
$result_user_tickets = $stmt_user_tickets->get_result();
$user_tickets_data = $result_user_tickets->fetch_assoc();
$user_tickets = $user_tickets_data['user_tickets'];
$total_spent = $user_tickets_data['total_spent'] ?? 0;

// Вычисляем шанс выигрыша
$percent = $total_tickets > 0 ? $user_tickets / $total_tickets : 0;

// Получаем баланс пользователя
$stmt_balance = $MySQLi->prepare("SELECT sum FROM users_balances WHERE id_user = ? AND id_balance = 3");
$stmt_balance->bind_param('i', $id_user);
$stmt_balance->execute();
$result_balance = $stmt_balance->get_result();
$balance_data = $result_balance->fetch_assoc();
$balance = $balance_data['sum'] ?? 0;

// Получаем возможные цены билетов для текущего пула
$stmt_ticket_prices = $MySQLi->prepare("SELECT t.sum 
                                        FROM estatepool_tickets t
                                        JOIN estatepool_usertickets ut ON t.id = ut.id_ticket
                                        WHERE ut.id_pool = ? 
                                        ORDER BY t.sum ASC");
$stmt_ticket_prices->bind_param('i', $id_pool);
$stmt_ticket_prices->execute();
$result_ticket_prices = $stmt_ticket_prices->get_result();
$ticket_prices = [];
while ($row = $result_ticket_prices->fetch_assoc()) {
    $ticket_prices[] = $row['sum'];
}

// Автоматическая покупка билетов, если баланс позволяет
$autobalance = 0;
if ($balance > 0 && count($ticket_prices) > 0) {
    foreach ($ticket_prices as $price) {
        if ($balance >= $price) {
            $count_tickets = floor($balance / $price); // Количество билетов, которое можно купить
            if ($count_tickets > 0) {
                // Покупаем билеты
                $autobalance = $price * $count_tickets;
                // Снимаем с баланса
                $new_balance = $balance - $autobalance;
                $stmt_update_balance = $MySQLi->prepare("UPDATE users_balances SET balance = ? WHERE id_user = ? AND id_balance = 3");
                $stmt_update_balance->bind_param('di', $new_balance, $id_user);
                $stmt_update_balance->execute();

                // Генерируем новые билеты для пользователя
                for ($i = 0; $i < $count_tickets; $i++) {
                    $stmt_add_ticket = $MySQLi->prepare("INSERT INTO estatepool_usertickets (id_user, id_pool, id_ticket) VALUES (?, ?, (SELECT id FROM estatepool_tickets WHERE id_pool = ? AND sum = ? LIMIT 1))");
                    $stmt_add_ticket->bind_param('iiii', $id_user, $id_pool, $id_pool, $price);
                    $stmt_add_ticket->execute();
                }
            }
        }
    }
}

// Формируем результат
$response = [
    'success' => true,
    'id_pool' => $id_pool,
    'id_user' => $id_user,
    'count_tickets' => $user_tickets,
    'percent' => $percent,
    'sum' => $total_spent,
    'autobalance' => $autobalance
];

// Закрытие соединения
$MySQLi->close();

// Выводим результат в JSON
echo json_encode($response);

?>