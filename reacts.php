<?php

$conn = new mysqli('localhost', 'zhalgas', '2286', 'mydb', 3310);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$video_id = isset($_POST['video_id']) ? (int) $_POST['video_id'] : 0;
$user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
$type = isset($_POST['type']) ? $_POST['type'] : '';


if ($video_id && $user_id && in_array($type, ['like', 'dislike'])) {
    

    $check_sql = "SELECT * FROM video_likes WHERE video_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $video_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $update_sql = "UPDATE video_likes SET type = ? WHERE video_id = ? AND user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sii", $type, $video_id, $user_id);
        $update_stmt->execute();
    } else {
        $insert_sql = "INSERT INTO video_likes (video_id, user_id, type) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iis", $video_id, $user_id, $type);
        $insert_stmt->execute();
    }

    $like_count_sql = "SELECT COUNT(*) AS likes_count FROM video_likes WHERE video_id = ? AND type = 'like'";
    $like_count_stmt = $conn->prepare($like_count_sql);
    $like_count_stmt->bind_param("i", $video_id);
    $like_count_stmt->execute();
    $like_count_result = $like_count_stmt->get_result();
    $like_count = $like_count_result->fetch_assoc()['likes_count'];

    $dislike_count_sql = "SELECT COUNT(*) AS dislikes_count FROM video_likes WHERE video_id = ? AND type = 'dislike'";
    $dislike_count_stmt = $conn->prepare($dislike_count_sql);
    $dislike_count_stmt->bind_param("i", $video_id);
    $dislike_count_stmt->execute();
    $dislike_count_result = $dislike_count_stmt->get_result();
    $dislike_count = $dislike_count_result->fetch_assoc()['dislikes_count'];

    $update_video_sql = "UPDATE videos SET likes_count = ?, dislikes_count = ? WHERE id = ?";
    $update_video_stmt = $conn->prepare($update_video_sql);
    $update_video_stmt->bind_param("iii", $like_count, $dislike_count, $video_id);
    $update_video_stmt->execute();


    echo json_encode([
        'success' => true,
        'likes' => $like_count,
        'dislikes' => $dislike_count,
        'user_reaction' => $type,
    ]);
} else {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid input data.'
    ]);
}

$conn->close();
?>
