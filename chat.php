<?php
session_start();
include 'includes/db_connection.php';
$conn = connectDB();
$profileLink="";
$profilePic = 'default_photo.jpg';
if (isset($_SESSION['user_id'])) {
    $profileLink = 'profile.php?id=' . $_SESSION['user_id'];
    $sql = "SELECT fname, lname, email, profile_pic FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!empty($user['profile_pic'])) {
        $profilePic = $user['profile_pic'];
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$profileLin = ''; 
$user_id = $_SESSION['user_id'];


$send_video = isset($_GET['send_video']) ? (int)$_GET['send_video'] : 0;
$video_slug = isset($_GET['video_slug']) ? $_GET['video_slug'] : '';

$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;
$receiver = null;

if ($receiver_id) {
    $sql = "SELECT id,fname, lname,profile_pic FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $receiver = $stmt->get_result()->fetch_assoc();

    if (!$receiver) {
        die("User not found.");
    }


   $sql = "SELECT sender_id, receiver_id, message, is_video, is_read, timestamp, 
               DATE(timestamp) AS message_date
        FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY timestamp ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $messages = $stmt->get_result();
    
    $update_sql = "UPDATE messages 
    SET is_read = 1 
    WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $receiver_id, $user_id);
    $update_stmt->execute();

    if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
        while ($msg = $messages->fetch_assoc()) {
            echo '<div class="' . ($msg['sender_id'] == $user_id ? 'sent' : 'received') . '">';
            if ($msg['is_video']) {
                $message = '<div class="video-message"><a href="' . htmlspecialchars($msg['message']) . '" target="_blank">' . htmlspecialchars($msg['message']) . '</a></div>';
            } else {
                $message = htmlspecialchars($msg['message']);
            }
            echo '<p>' . $message . '</p>';
            echo '<div class="message-footer">';
            echo '<small>' . date("H:i", strtotime($msg['timestamp']));
            if ($msg['sender_id'] == $user_id && $msg['is_read'] == 1) {
                echo ' <span class="read-status">✔</span>';
            }
            echo '</small>';
            echo '</div>';
            echo '</div>';
        }
        exit;
    }
    
}
$profileLin = 'profile.php?id=' . $receiver['id'];

$sql = "SELECT u.id, u.fname, u.lname, u.profile_pic, 
               COALESCE(
                   (SELECT COUNT(*) 
                    FROM messages 
                    WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0),
                   0
               ) AS unread_count,
               COALESCE(
                   (SELECT message 
                    FROM messages 
                    WHERE (sender_id = u.id AND receiver_id = ?) 
                    OR (sender_id = ? AND receiver_id = u.id) 
                    ORDER BY timestamp DESC LIMIT 1), 
                   ''
               ) AS last_message,
               COALESCE(
                   (SELECT timestamp 
                    FROM messages 
                    WHERE (sender_id = u.id AND receiver_id = ?) 
                    OR (sender_id = ? AND receiver_id = u.id) 
                    ORDER BY timestamp DESC LIMIT 1), 
                   ''
               ) AS last_message_time
        FROM users u
        WHERE u.id != ?
        ORDER BY last_message_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id); // 6 параметров
$stmt->execute();
$result = $stmt->get_result();




?>



<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="chat.css">
    <link rel="stylesheet" href="style2.css">
    <script src="mob.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body class="chat_body">
    
<nav class="navbar navbar-dark fixed-top">
      <div class="ncontent">
          <a class="navbar-brand" href="index.php">
            <img src="logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
            Chattrix
          </a>
   
          <div class="navigation" id="navigation">
            <a class="icon" href="index.php"><i class="fa-solid fa-house"></i></a>
            <a class="icon" href="index.php?following=true"><i class="fa-solid fa-user-group"></i></a>
            <a class="icon" href="index.php?liked=true"><i class="fa-solid fa-heart"></i></a>
            <a class="icon" href="chat.php?receiver_id=45"><i class="fa-solid fa-envelope"></i></a>
            
          </div>
          <form class="form-inline" id="sarch" action="index.php" method="GET">
            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" id="sarchbar" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button id="sbs" class="btn btn-outline-success my-2 my-sm-0" type="submit"><i id="sb1" class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <div>
        <?php if (isset($_SESSION['user_id'])): ?>
    <div class="dropdown">
        <a href="#" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
        <img id="dimg" src="upload/profile_pics/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" width="35" height="35" class="rounded-circle mr-2">
        </a>
        <ul class="dropdown-menu dropdown-menu-end " aria-labelledby="dropdownMenuButton" style="right: 0; left: auto; top:44px;">
            <li><a class="dropdown-item " href="<?php echo $profileLink; ?>">            
                <div class="d1">
                    <img src="upload/profile_pics/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" width="50" height="50" class="rounded-circle mr-2">
                    <h5><?php echo htmlspecialchars($user['fname'])." ".htmlspecialchars($user['lname']) ?></h5>
                    <p class="dmail"><?php echo htmlspecialchars($user['email'])?></p>
                </div>
            </a></li>
            <li><li><a class="dropdown-item" href="<?php echo $profileLink; ?>"><i id="logicon" class="fa-solid fa-user"></i> Open Profile</a></li>
            <div class="dropdown-divider"></div>
            <li><a class="dropdown-item" href="logout.php"><i id="logicon" class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </div>
<?php else: ?>
    <a href="login.php" class="btn btn-outline-light my-2 my-sm-0 ml-3">Login</a>
<?php endif; ?>



        </div>
      </div>
    </nav>
<div class="chat_main">
    <div class="chatleft">
            <?php if ($send_video && $video_slug): ?>
                <p>You are about to send the following video link: <strong>http://192.168.189.145:8000//video.php?slug=<?php echo urlencode($video_slug); ?></strong></p>
                <form id="messageForm" method="POST" action="send_message.php">
                    <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
                    <textarea name="message" placeholder="Type your message..." required></textarea>
                    <button type="submit">Send</button>
                </form>
            <?php else: ?>
    <ul class="kotakbas">
    <?php while ($row = $result->fetch_assoc()): ?>
        <li class="rel">
            <a class="relel"  href="chat.php?receiver_id=<?= $row['id'] ?>">
                <img class="img" src="upload/profile_pics/<?php echo htmlspecialchars($row['profile_pic']); ?>" alt="Profile Picture" width="50" height="50">
                <div class="relcon">
                    <?= htmlspecialchars($row['fname'] . " " . $row['lname']) ?>
                    <span class="last-message"><?= htmlspecialchars(substr($row['last_message'], 0, 20)) . (strlen($row['last_message']) > 20 ? '...' : '') ?></span>
                    <?php if ($row['unread_count'] > 0): ?>
                        <span class="badge"><?= $row['unread_count'] ?></span>
                    <?php endif; ?>
                </div>
            </a>
        </li>
    <?php endwhile; ?>
</ul>


            <?php endif; ?>
        </div>
        <div class="chat-container">
            
            <div class="chatup">
                <a class="chatupim" href="<?php echo $profileLin; ?>"><img class="img" src="upload/profile_pics/<?php echo htmlspecialchars($receiver['profile_pic']); ?>" alt="Profile Picture""></a>
                <h2 class="cname"><?= htmlspecialchars($receiver['fname'] . " " . $receiver['lname']) ?></h2>
            </div>
            <div class="chat-box">
            <?php

$previous_date = null;
while ($msg = $messages->fetch_assoc()) {
    $message_date = $msg['message_date'];
    if ($previous_date !== $message_date) {
        echo '<div class="message-date">' . date("F j, Y", strtotime($message_date)) . '</div>';
        $previous_date = $message_date;
    }

    echo '<div class="' . ($msg['sender_id'] == $user_id ? 'sent' : 'received') . '">';
    
    if ($msg['is_video']) {
        echo '<div class="video-message"><a href="' . htmlspecialchars($msg['message']) . '" target="_blank">' . htmlspecialchars($msg['message']) . '</a></div>';
    } else {
        echo '<p>' . htmlspecialchars($msg['message']) . '</p>';
    }

    echo '<div class="message-footer">';
    echo '<small>';
    echo date("H:i", strtotime($msg['timestamp']));
    if ($msg['sender_id'] == $user_id && isset($msg['is_read']) && $msg['is_read'] == 1) {
        echo ' <span class="read-status">✔</span>';
    }
    echo '</small>';
    echo '</div>';
    echo '</div>';
}


?>

            </div>
            <form id="messageForm" method="POST" action="send_message.php">
                <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
                <textarea class="textarea" name="message" placeholder="Message" required></textarea>
                <button id="send_button" type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
</div>

    <script>


function scrollToBottom() {
    const chatBox = document.querySelector('.chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;
}

window.onload = function() {
    scrollToBottom(); 
};

setInterval(function() {
    loadMessages();   
    scrollToBottom();  
}, 5000);




document.getElementById('messageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('send_message.php', {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.ok) {
            this.reset();
            loadMessages();
        }
    });
});

function loadMessages() {
    fetch('chat.php?receiver_id=<?= $receiver_id ?>&ajax=1')
        .then(response => response.text())
        .then(data => {
            document.querySelector('.chat-box').innerHTML = data;
            setTimeout(loadMessages, 3000);
        });
}




function loadLastMessage() {
    fetch('get_last_message.php?receiver_id=<?= $receiver_id ?>')
        .then(response => response.text())
        .then(lastMessage => {
            const lastMessageElement = document.querySelector('.last-message');
            if (lastMessageElement) {
                lastMessageElement.textContent = lastMessage.length > 20 ? lastMessage.substring(0, 20) + '...' : lastMessage;
            }
        });
}

setInterval(loadLastMessage, 3000);
function loadChatList() {
    fetch('chat_list.php')
        .then(response => response.text())
        .then(data => {
            document.querySelector('.kotakbas').innerHTML = data;
        });
}

setInterval(loadChatList, 3000);


</script>

</body>
</html>
