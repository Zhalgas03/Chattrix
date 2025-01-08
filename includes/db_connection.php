<?php
function connectDB() {
    $servername = "localhost"; // Имя сервера базы данных
    $username = "zhalgas";     // Имя пользователя базы данных
    $password = "2286";        // Пароль базы данных
    $dbname = "mydb";          // Имя базы данных
    $port = 3310;              // Порт базы данных (если используется нестандартный)

    // Создаём подключение
    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    // Проверяем подключение
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>
