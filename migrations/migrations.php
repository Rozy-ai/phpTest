<?php

include '../config.php';

$MySQLi = new mysqli('localhost',$DB['username'],$DB['password'],$DB['dbname']);
$MySQLi->query("SET NAMES 'utf8'");
$MySQLi->set_charset('utf8mb4');

if ($MySQLi->connect_error) {
    die("Ошибка подключения: " . $MySQLi->connect_error);
}

$dropTables = [
    "DROP TABLE IF EXISTS `estatepool_usertickets`",
    "DROP TABLE IF EXISTS `estatepool_tickets`",
    "DROP TABLE IF EXISTS `estatepool`",
    "DROP TABLE IF EXISTS `estatepool_gifts`",
    "DROP TABLE IF EXISTS `balances`",
    "DROP TABLE IF EXISTS `users`",
    "DROP TABLE IF EXISTS `users_balances`"
];

$tables = [
    "CREATE TABLE `estatepool` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `date_start` DATETIME NOT NULL,
        `date_close` DATETIME NOT NULL,
        `sum` DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
        `sum_goal` DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
        `status` TINYINT(1) NOT NULL DEFAULT 0
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "CREATE TABLE `estatepool_tickets` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `count` INT UNSIGNED NOT NULL DEFAULT 0,
        `sum` DECIMAL(10,6) NOT NULL DEFAULT 0.00,
        `status` TINYINT(1) NOT NULL DEFAULT 0
      )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "CREATE TABLE `estatepool_usertickets` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `ticket` VARCHAR(9) NOT NULL,
        `id_ticket` INT UNSIGNED NULL COMMENT 'ID билета',
        `id_user` INT UNSIGNED NULL,
        `id_pool` INT UNSIGNED NOT NULL,
        `id_gift` INT UNSIGNED NOT NULL COMMENT 'ID подарка',  
        `win` TINYINT(1) NOT NULL DEFAULT 0,
        CONSTRAINT `fk_estatepool_usertickets_pool` FOREIGN KEY (`id_pool`) REFERENCES `estatepool` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
      )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "CREATE TABLE `estatepool_gifts` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `id_pool` INT UNSIGNED NOT NULL,
        `name` VARCHAR(255) NOT NULL COMMENT 'Имя подарка EN', 
        `date_close` VARCHAR(30) NOT NULL COMMENT 'Дата определения победителя',
        `id_winner` INT UNSIGNED NULL COMMENT 'ID победителя',        
        `id_not_winner` INT UNSIGNED NULL COMMENT 'ID точного победителя',    
        `priority` int NOT NULL DEFAULT '0' COMMENT 'Нумерация порядка',
        `general` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 простой, 1 основной',
        CONSTRAINT `fk_estatepool_gifts_pool` FOREIGN KEY (`id_pool`) REFERENCES `estatepool` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
      )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
      "CREATE TABLE `balances` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `title` varchar(255) DEFAULT NULL COMMENT 'Описание баланса',
        `paysystem` varchar(255) DEFAULT NULL COMMENT 'Кодовое название баланса',
        `currency` varchar(20) DEFAULT NULL COMMENT 'Тикер курса',
        `status` int NOT NULL DEFAULT '0' COMMENT '1 включена, 0 отключена',
        `type` int NOT NULL DEFAULT '1' COMMENT '1 - фиат, 2 - криптовалюта, 3 - платежная система'
      )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
      "CREATE TABLE `users` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `email` varchar(255) DEFAULT NULL,
        `id_ref` int DEFAULT '0' COMMENT 'id пригласителя'clea
      )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
      "CREATE TABLE `users_balances` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `id_user` int DEFAULT NULL COMMENT 'ID пользователя',
        `id_balance` int DEFAULT NULL COMMENT 'ID балансов таблица balances',
        `sum` decimal(20,10) NOT NULL DEFAULT '0.0000000000' COMMENT 'Сумма баланса',
        `stat_sum` decimal(20,10) DEFAULT '0.0000000000' COMMENT 'Статистика баланса',
        `status` int NOT NULL DEFAULT '1' COMMENT '1 работает, 0 заблокирован',
        `show_balance` int NOT NULL DEFAULT '1' COMMENT '1 - показан, 0 - спрятан',
        CONSTRAINT `fk_users_balances` FOREIGN KEY (`id_balance`) REFERENCES `balances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
      )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($dropTables as $sql) {
    if ($MySQLi->query($sql) === TRUE) {
        echo "Таблица успешно удалена (если существовала).<br>";
    } else {
        echo "Ошибка удаления таблицы: " . $MySQLi->error . "<br>";
    }
}

foreach ($tables as $sql) {
    if ($MySQLi->query($sql) === TRUE) {
        echo "Таблица успешно создана.<br>";
    } else {
        echo "Ошибка при создании таблицы: " . $MySQLi->error . "<br>";
    }
}










$MySQLi->close();