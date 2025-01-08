<?php
session_start();
include 'includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = connectDB();

$send_video = isset($_GET['send_video']) ? (int)$_GET['send_video'] : 0;
$video_slug = isset($_GET['video_slug']) ? $_GET['video_slug'] : '';

$sql = "SELECT id, fname, lname FROM users WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
</head>
<body>
    <h1>Chat with other users</h1>

    <?php if ($send_video && $video_slug): ?>
        <p>You are about to send the following video link: <strong>http://192.168.189.145:8000//video.php?slug=<?php echo urlencode($video_slug); ?></strong></p>
        <form action="send_message.php" method="POST">
            <input type="hidden" name="video_slug" value="<?php echo urlencode($video_slug); ?>">
            <label for="receiver_id">Choose a recipient:</label>
            <select name="receiver_id" id="receiver_id">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['fname'] . " " . $row['lname']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Send Video</button>
        </form>
    <?php endif; ?>
</body>
</html>
