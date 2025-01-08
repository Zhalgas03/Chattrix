<?php
session_start();
include 'includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = connectDB();

$sql = "SELECT 
            u.id, u.fname, u.lname, u.profile_pic, 
            COALESCE((
                SELECT message 
                FROM messages 
                WHERE (sender_id = u.id AND receiver_id = ?) 
                   OR (sender_id = ? AND receiver_id = u.id) 
                ORDER BY timestamp DESC LIMIT 1
            ), '') AS last_message,
            COALESCE((
                SELECT COUNT(*) 
                FROM messages 
                WHERE sender_id = u.id 
                  AND receiver_id = ? 
                  AND is_read = 0
            ), 0) AS unread_count
        FROM users u
        WHERE u.id != ?
        ORDER BY last_message DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()): ?>
    <li class="rel" onclick="openChat(event)">
        <a class="relel" href="chat.php?receiver_id=<?= $row['id'] ?>">
            <img class="img" src="upload/profile_pics/<?= htmlspecialchars($row['profile_pic']) ?>" alt="Profile Picture" width="50" height="50">
            <div class="relcon">
                <?= htmlspecialchars($row['fname'] . " " . $row['lname']) ?>
                <span class="last-message">
                    <?= htmlspecialchars(substr($row['last_message'], 0, 20)) ?>
                </span>
                <?php if ($row['unread_count'] > 0): ?>
                    <span class="unread-count">(<?= $row['unread_count'] ?>)</span>
                <?php endif; ?>
            </div>
        </a>
    </li>
<?php endwhile; ?>


