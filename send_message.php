<?php
session_start();
include 'includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$video_slug = isset($_POST['video_slug']) ? $_POST['video_slug'] : '';
$message = isset($_POST['message']) ? $_POST['message'] : '';

$conn = connectDB();
$sql = "SELECT id FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    if (!empty($video_slug)) {
        $message = "http://192.168.189.145:8000//video.php?slug=" . urlencode($video_slug);
    }

    $insert_sql = "INSERT INTO messages (sender_id, receiver_id, message, is_video, timestamp) VALUES (?, ?, ?, ?, NOW())";
    $is_video = !empty($video_slug) ? 1 : 0; 
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iisi", $sender_id, $receiver_id, $message, $is_video);
    $insert_stmt->execute();

    header("Location: chat.php?receiver_id=" . $receiver_id);
    exit();
} else {
    echo "Получатель не найден!";
}
?>
