<?php
include ('includes/db_connection.php');

$conn = connectDB();


if (isset($_GET['id'])) {
    $comment_id = intval($_GET['id']); 

    $query = "DELETE FROM comments WHERE id = $comment_id";

    if ($conn->query($query) === TRUE) {
        echo "Комментарий успешно удалён.";
    } else {
        echo "Ошибка при удалении комментария: " . $conn->error;
    }
} else {
    echo "ID комментария не передан.";
}

$conn->close();
?>
