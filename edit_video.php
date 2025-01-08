<?php
include ('includes/db_connection.php');
$conn = connectDB();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM videos WHERE id = $id");
    $video = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $filename = $_POST['filename'];

    $conn->query("UPDATE videos SET title = '$title', filename = '$filename' WHERE id = $id");
    header('Location: dashboard.php');
    exit;
}
?>

<form method="post">
    <label>Название видео:</label>
    <input type="text" name="title" value="<?= $video['title'] ?>" required>
    <br>
    <label>Файл:</label>
    <input type="text" name="filename" value="<?= $video['filename'] ?>" required>
    <br>
    <button type="submit">Сохранить</button>
</form>
