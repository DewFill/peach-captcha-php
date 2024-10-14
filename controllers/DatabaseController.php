<?php

namespace Peach\Controllers;

use PDO;

class DatabaseController
{
    private null|PDO $pdo = null;
    private function initConnection()
    {
        // Настройки подключения
        $host = 'db'; // Хост
        $db = 'peach'; // Имя базы данных
        $user = 'root'; // Имя пользователя
        $pass = 'root'; // Пароль

        // Создание подключения
        $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }
    function getPDO(): PDO
    {
        if ($this->pdo == null) {
            $this->initConnection();
        }

        return $this->pdo;
    }
}