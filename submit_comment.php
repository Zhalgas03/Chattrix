<?php
session_start();
$conn = new mysqli('localhost', 'zhalgas', '2286', 'mydb', 3310);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'], $_POST['video_id'], $_POST['comment'])) {
    $user_id = $_SESSION['user_id'];
    $video_id = $_POST['video_id'];
    $comment = htmlspecialchars(trim($_POST['comment']));

    if (!empty($comment)) {
        $sql = "INSERT INTO comments (video_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $video_id, $user_id, $comment);

        if ($stmt->execute()) {
            $comment_id = $stmt->insert_id;
            $user_sql = "SELECT fname, profile_pic,id FROM users WHERE id = ?";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_data = $user_stmt->get_result()->fetch_assoc();

            echo json_encode([
                'success' => true,
                'comment_id' => $comment_id,
                'fname' => $user_data['fname'],
                'profile_pic' => $user_data['profile_pic'],
                'id'=> $user_data['id'],
                'comment' => $comment,
                'created_at' => 'Just now'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Comment is empty']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();

?>
