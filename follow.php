<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);


$conn = new mysqli('localhost', 'zhalgas', '2286', 'mydb', 3310);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You need to log in to subscribe.']);
    exit;
}


$user_id = $_SESSION['user_id'];
$channel_id = intval($_POST['channel_id']);
$action = $_POST['action'];

if ($action === 'follow') {
    $sql = "INSERT IGNORE INTO follows (user_id, channel_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $channel_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'action' => 'followed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to follow.']);
    }
    $stmt->close();
} elseif ($action === 'unfollow') {
    $sql = "DELETE FROM follows WHERE user_id = ? AND channel_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $channel_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'action' => 'unfollowed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to unfollow.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

$conn->close();
?>
