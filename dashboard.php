<?php
session_start();

include ('includes/db_connection.php');

$conn = connectDB();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM users WHERE id = $user_id";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if ($user['role'] != 1) {
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}

echo "<h1>Admin Panel</h1>";
echo "<a href='logout.php'>Logout</a>";
echo "<h2>User List</h2>";
echo "<table border='1'>
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Date of Birth</th>
        <th>Gender</th>
        <th>Phone</th>
        <th>Actions</th>
    </tr>";

$result = $conn->query("SELECT * FROM users");

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>" . $row['fname'] . "</td>
        <td>" . $row['lname'] . "</td>
        <td>" . $row['email'] . "</td>
        <td>" . $row['dob'] . "</td>
        <td>" . $row['gender'] . "</td>
        <td>" . $row['phone_number'] . "</td>
        <td><a href='edit_user.php?id=" . $row['id'] . "'>Edit</a> | <a href='delete_user.php?id=" . $row['id'] . "'>Delete</a></td>
    </tr>";
}

echo "</table>";

$video_result = $conn->query("SELECT * FROM videos");

echo "<h2>Video List</h2>";
echo "<table border='1'>
    <tr>
        <th>Title</th>
        <th>User</th>
        <th>Upload Date</th>
        <th>Views</th>
        <th>Likes</th>
        <th>Dislikes</th>
        <th>Actions</th>
    </tr>";

while ($video = $video_result->fetch_assoc()) {
    $user_result = $conn->query("SELECT fname, lname FROM users WHERE id = " . $video['user_id']);
    $user = $user_result->fetch_assoc();
    
    echo "<tr>
        <td>" . $video['title'] . "</td>
        <td>" . $user['fname'] . " " . $user['lname'] . "</td>
        <td>" . $video['upload_date'] . "</td>
        <td>" . $video['views'] . "</td>
        <td>" . $video['likes_count'] . "</td>
        <td>" . $video['dislikes_count'] . "</td>
        <td><a href='edit_video.php?id=" . $video['id'] . "'>Edit</a> | <a href='delete_video.php?id=" . $video['id'] . "'>Delete</a></td>
    </tr>";
}
echo "</table>";

$comment_result = $conn->query("SELECT * FROM comments");

echo "<h2>Comment List</h2>";
echo "<table border='1'>
    <tr>
        <th>Comment</th>
        <th>User</th>
        <th>Video</th>
        <th>Created At</th>
        <th>Actions</th>
    </tr>";

while ($comment = $comment_result->fetch_assoc()) {
    $user_result = $conn->query("SELECT fname, lname FROM users WHERE id = " . $comment['user_id']);
    $user = $user_result->fetch_assoc();
    
    $video_result = $conn->query("SELECT title FROM videos WHERE id = " . $comment['video_id']);
    $video = $video_result->fetch_assoc();

    echo "<tr>
        <td>" . $comment['comment'] . "</td>
        <td>" . $user['fname'] . " " . $user['lname'] . "</td>
        <td>" . $video['title'] . "</td>
        <td>" . $comment['created_at'] . "</td>
        <td><a href='delete_comment.php?id=" . $comment['id'] . "'>Delete</a></td>
    </tr>";
}
echo "</table>";

$message_result = $conn->query("SELECT * FROM messages");

echo "<h2>Message List</h2>";
echo "<table border='1'>
    <tr>
        <th>Message</th>
        <th>Sender</th>
        <th>Receiver</th>
        <th>Sent At</th>
    </tr>";

while ($message = $message_result->fetch_assoc()) {
    $sender_result = $conn->query("SELECT fname, lname FROM users WHERE id = " . $message['sender_id']);
    $sender = $sender_result->fetch_assoc();
    
    $receiver_result = $conn->query("SELECT fname, lname FROM users WHERE id = " . $message['receiver_id']);
    $receiver = $receiver_result->fetch_assoc();

    echo "<tr>
        <td>" . $message['message'] . "</td>
        <td>" . $sender['fname'] . " " . $sender['lname'] . "</td>
        <td>" . $receiver['fname'] . " " . $receiver['lname'] . "</td>
        <td>" . $message['timestamp'] . "</td>
    </tr>";
}
echo "</table>";
?>
