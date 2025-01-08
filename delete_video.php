<?php
include ('includes/db_connection.php');

$conn = connectDB();

if (isset($_GET['id'])) {
    $video_id = intval($_GET['id']); 

    $query = "SELECT filename FROM videos WHERE id = $video_id";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $filename = $row['filename'];

        $conn->query("DELETE FROM comments WHERE video_id = $video_id");
        $conn->query("DELETE FROM video_likes WHERE video_id = $video_id");

        if ($conn->query("DELETE FROM videos WHERE id = $video_id") === TRUE) {

            $filePath = "uploads/videos/" . $filename; 
            if (file_exists($filePath)) {
                unlink($filePath); 
            }
            echo "Видео успешно удалено.";
        } else {
            echo "Ошибка при удалении видео: " . $conn->error;
        }
    } else {
        echo "Видео не найдено.";
    }
} else {
    echo "ID видео не передан.";
}

$conn->close();
?>
