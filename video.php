<?php
session_start();

function timeAgo($timestamp) {
    $time = time() - strtotime($timestamp);

    if ($time < 60) return $time . ' seconds ago';
    $time = round($time / 60);
    if ($time < 60) return $time . ' minutes ago';
    $time = round($time / 60);
    if ($time < 24) return $time . ' hours ago';
    $time = round($time / 24);
    if ($time < 30) return $time . ' days ago';
    $time = round($time / 30);
    if ($time < 12) return $time . ' months ago';
    $time = round($time / 12);
    return $time . ' years ago';
}


if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
} else {
    die("Video not found.");
}

$conn = new mysqli('localhost', 'zhalgas', '2286', 'mydb', 3310);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$profileLink = ''; 
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

$sql = "
    SELECT  
        v.id,
        v.filename,
        v.slug, 
        v.views, 
        v.title, 
        v.user_id,
        v.likes_count, 
        v.dislikes_count, 
        v.upload_date,
        u.id AS channel_id,
        u.fname, 
        u.profile_pic, 
        u.profile_slug
    FROM videos v
    JOIN users u ON v.user_id = u.id
    WHERE v.slug = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $video = $result->fetch_assoc();
    $new_views = $video['views'] + 1;
    $update_sql = "UPDATE videos SET views = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $new_views, $video['id']);
    $update_stmt->execute();


    $video['views'] = $new_views;

    $like_sql = "SELECT COUNT(*) FROM video_likes WHERE video_id = ? AND type = 'like'";
    $dislike_sql = "SELECT COUNT(*) FROM video_likes WHERE video_id = ? AND type = 'dislike'";

    $like_stmt = $conn->prepare($like_sql);
    $like_stmt->bind_param("i", $video['id']);
    $like_stmt->execute();
    $like_result = $like_stmt->get_result();
    $like_count = $like_result->fetch_row()[0];

    $dislike_stmt = $conn->prepare($dislike_sql);
    $dislike_stmt->bind_param("i", $video['id']);
    $dislike_stmt->execute();
    $dislike_result = $dislike_stmt->get_result();
    $dislike_count = $dislike_result->fetch_row()[0];

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $user_reaction = null;

    if ($user_id) {
        $user_reaction_sql = "SELECT type FROM video_likes WHERE video_id = ? AND user_id = ?";
        $user_reaction_stmt = $conn->prepare($user_reaction_sql);
        $user_reaction_stmt->bind_param("ii", $video['id'], $user_id);
        $user_reaction_stmt->execute();
        $user_reaction_result = $user_reaction_stmt->get_result();

        if ($user_reaction_result->num_rows > 0) {
            $user_reaction = $user_reaction_result->fetch_assoc()['type'];
        }
    }
} else {
     http_response_code(404);
        echo "Video not found.";
}
$channel_id = $video['channel_id'];
$is_following = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $follow_sql = "SELECT * FROM follows WHERE user_id = ? AND channel_id = ?";
    $follow_stmt = $conn->prepare($follow_sql);
    $follow_stmt->bind_param("ii", $user_id, $channel_id);
    $follow_stmt->execute();
    $follow_result = $follow_stmt->get_result();
    $is_following = $follow_result->num_rows > 0;
    $follow_stmt->close();
}
$subscribers_sql = "SELECT COUNT(*) AS subscriber_count FROM follows WHERE channel_id = ?";
$subscribers_stmt = $conn->prepare($subscribers_sql);
$subscribers_stmt->bind_param("i", $video['user_id']);
$subscribers_stmt->execute();
$subscribers_result = $subscribers_stmt->get_result();
$subscribers_data = $subscribers_result->fetch_assoc();
$subscribers_count = $subscribers_data['subscriber_count'];
$subscribers_stmt->close();

$is_owner = $video['user_id'] == $_SESSION['user_id'];
$comments_sql = "
    SELECT c.comment, c.created_at, u.fname, u.profile_pic,u.id 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.video_id = ?
    ORDER BY c.created_at DESC
";
$comments_stmt = $conn->prepare($comments_sql);
$comments_stmt->bind_param("i", $video['id']);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();


$sql_count = "SELECT COUNT(*) AS comment_count FROM comments WHERE video_id = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $video['id']);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$count_row = $count_result->fetch_assoc();
$comment_count = $count_row['comment_count'];


$comments_stmt->close();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['title']); ?></title>
    <link rel="stylesheet" href="style2.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="follow.js"></script>
    <script src="mob.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bb">


<div class="video_main">
    <nav class="navbar navbar-dark fixed-top">
      <div class="ncontent">
          <a id="logo" class="navbar-brand" href="index.php">
            <img src="logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
            Chattrix
          </a>
   
          <div class="navigation">
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
        <a href="#"  id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
        <img id="dimg" src="upload/profile_pics/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" width="35" height="35" class="rounded-circle mr-2">
        </a>
        <ul  class="dropdown-menu dropdown-menu-end " aria-labelledby="dropdownMenuButton" style="right: 0; left: auto; top:44px;">
            <li><a class="dropdown-item " href="<?php echo $profileLink; ?>">            
                <div class="d1">
                    <img src="upload/profile_pics/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" width="50" height="50" class="rounded-circle mr-2">
                    <h5><?php echo htmlspecialchars($user['fname'])." ".htmlspecialchars($user['lname']) ?></h5>
                    <p class="dmail"><?php echo htmlspecialchars($user['email'])?></p>
                </div>
            </a></li>
            <li><li> <a class="dropdown-item" href="<?php echo $profileLink; ?>"><i id="logicon" class="fa-solid fa-user"></i> Open Profile</a></li>
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

        <video id="player" controls preload="metadata" autoplay>
            <source src="stream.php?file=<?php echo urlencode($video['filename']); ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <h1 class="vtit" style="margin:10px 0px 12px 0px;"><?php echo htmlspecialchars($video['title']); ?></h1>

        <div class="user-info">
            <div class="info1">
                <a id="a1" href="profile.php?id=<?php echo $video['user_id']; ?>"><img  src="upload/profile_pics/<?php echo htmlspecialchars($video['profile_pic']); ?>" alt="Profile Picture" width="40" height="40" class="rounded-circle mr-2"></a>
                <div>
                    <h5><?php echo htmlspecialchars($video['fname']).htmlspecialchars($video['user_id']); ?></h5>
                    <p class="followers-info">
                        <span><?php echo $subscribers_count; ?></span> followers
                    </p>
                </div>
            </div>

            <div class="info3">
                <div class="channel-info">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($channel_id != $_SESSION['user_id']): ?>
                <button id="follow-btn" class="<?php echo $is_following ? 'subscribed' : ''; ?>" onclick="toggleFollow(<?php echo $channel_id; ?>, '<?php echo $is_following ? 'unfollow' : 'follow'; ?>')">
                    <?php echo $is_following ? 'Unfollow' : 'Follow'; ?>
                </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><a href="/login.php">Log in</a> to follow this channel.</p>
                    <?php endif; ?>
                </div>

                
                
                        <?php if ($user_id): ?>
                      <div class="eee">
                          <div class="reactions">
                            <button
                                          class="like-btn <?php echo ($user_reaction === 'like') ? 'active' : ''; ?>"
                                          data-video-id="<?php echo $video['id']; ?>"
                                          data-type="like">
                                          <i class="fa-regular fa-thumbs-up"></i>
                                          <span id="likes-count"><?php echo $video['likes_count']; ?></span>
                            </button>
                            <button
                                          class="dislike-btn <?php echo ($user_reaction === 'dislike') ? 'active' : ''; ?>"
                                          data-video-id="<?php echo $video['id']; ?>"
                                          data-type="dislike">
                                          <i class="fa-regular fa-thumbs-down"></i>
                                          <span id="dislikes-count"><?php echo $video['dislikes_count']; ?></span>
                            </button>
                                              </div>
                                              <a href="users.php?send_video=1&video_slug=<?php echo urlencode($slug); ?>" id="sharebtn" class="btn"><i style="margin-right:7px;" class="fa-solid fa-share"></i>Share</a>
                      </div>
            </div>
            <div>
               
            


   

            
        <?php else: ?>
            <p><a href="login.php">Login to react</a></p>
        <?php endif; ?>
        
        </div>
    </div>
        <div class="info2">
            <p><?php echo htmlspecialchars($video['views']); ?> views </p>
            <p style="margin-left:15px;"><?php echo date('F j, Y', strtotime($video['upload_date'])); ?></p>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
    <div class="comment-section">
        <h3 style="font-size:22px;"><?php echo $comment_count; ?> comments</h3>
        <div class="com" style="margin-bottom:30px;">
            <img class="cimg" src="upload/profile_pics/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture">
            <form class="comform" id="comment-form">
    <textarea class="comin" id="comment-text" name="comment" placeholder="Write a comment..." required></textarea>
    <input type="hidden" id="video-id" name="video_id" value="<?php echo $video['id']; ?>">
    
    <div class="cbtndiv">
        <button type="button" class="cancel-btn" onclick="clearComment()">Clear</button>
        <button class="combtn" type="submit" disabled>Leave a comment</button></div>
</form>

        </div>
    </div>

<div class="comments-section">   
    <?php
if ($comments_result->num_rows > 0) {
    while ($comment = $comments_result->fetch_assoc()) {
        $relative_time = timeAgo($comment['created_at']);
        echo "
    <div class='comment-wrapper'>
    <a href='profile.php?id=" . htmlspecialchars($comment['id']) . "'>
        <img src='upload/profile_pics/" . htmlspecialchars($comment['profile_pic']) . "' alt='Profile Picture' class='compic'>
    </a>
        <div class='commento'>
            <div class='comment-header'>
                <div class='comment-author'>
                      <a class='cname' href='profile.php?id=" . htmlspecialchars($comment['id']) . "'>
            <strong>" . htmlspecialchars($comment['fname']) . "</strong>
        </a>
                    <em style='font-size:13px; position:relative; top:3px;' class='comment-date'>" . htmlspecialchars($relative_time) . "</em>
                </div>
            </div>
            <div class='comment-body'>
                <p >" . nl2br(htmlspecialchars($comment['comment'])) . "</p>
            </div>
        </div>
    </div>
    <hr class='comment-divider'>
    ";
    }
} else {
    echo "<p>No comments yet.</p>";
}
?>
</div>


<?php else: ?>
    <p><a href="login.php">Log in</a> to comment.</p>
<?php endif; ?>

    </div>

    <script>
$(document).ready(function() {
  const $textarea = $('.comin');
  const $submitBtn = $('.combtn');

  $textarea.on('input', function() {
    if ($textarea.val().trim() !== '') {
      $submitBtn.prop('disabled', false);
      $submitBtn.css('background-color', '#007bff');  
    } else {
      $submitBtn.prop('disabled', true);
      $submitBtn.css('background-color', '#333333'); 
    }
  });
});

function clearComment() {
    $('.comin').val('');
}

$(document).ready(function() {
    $('#comment-form').submit(function(e) {
        e.preventDefault();

        var comment = $('#comment-text').val();
        var videoId = $('#video-id').val();

        $.ajax({
            url: 'submit_comment.php',
            type: 'POST',
            dataType: 'json',
            data: { comment: comment, video_id: videoId },
            success: function(response) {
                if (response.success) {
                    var newComment = `
                        <div class="comment-wrapper">
                            <a href="profile.php?id=${response.id}">
                                <img src="upload/profile_pics/${response.profile_pic}" alt="Profile Picture" class="compic">
                            </a>
                            <div class="commento">
                            <div class="comment-header">
                                <div class="comment-author">
                                <a class='cname' href="profile.php?id=${response.id}">
                               <strong>${response.fname}</strong><br>
                            </a>
                                    
                                    <em class="comment-date">${response.created_at}</em>
                                </div>
                            </div>
                            <div class="comment-body">
                                <p>${response.comment}</p>
                            </div>
                        </div>
                        </div>
                        <hr class="comment-divider">

                        
                    `;
                    $('.comments-section').prepend(newComment);
                    $('#comment-text').val(''); 
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });
});

function toggleFollow(channelId, action) {
    $.ajax({
        url: '/follow.php',
        type: 'POST',
        data: { channel_id: channelId, action: action },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const followBtn = document.getElementById('follow-btn');
                if (response.action === 'followed') {
                    followBtn.classList.add("subscribed");
                    followBtn.textContent = 'Unfollow';
                    followBtn.setAttribute('onclick', `toggleFollow(${channelId}, 'unfollow')`);
                } else if (response.action === 'unfollowed') {
                    followBtn.classList.remove("subscribed");
                    followBtn.textContent = 'Follow';
                    followBtn.setAttribute('onclick', `toggleFollow(${channelId}, 'follow')`);
                }
            } else {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
}
$(document).ready(function() {
    $(document).on("fullscreenchange webkitfullscreenchange mozfullscreenchange msfullscreenchange", function() {
        var $video = $("#player");

        if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
            $video.css({
                "width": "100%",
                "height": "auto"
            });
        } else {
            if (window.matchMedia("(max-width: 768px)").matches) {
                $video.css({
                    "width": "100%", 
                    "height": "auto"
                });
            } else {
                $video.css({
                    "width": "1244px",
                    "height": "700px" 
                });
            }
        }
    });




    $('.like-btn').on('click', function() {
        var videoId = $(this).data('video-id');
        var type = 'like'; 

        var userId = <?php echo $_SESSION['user_id']; ?>;

        $.ajax({
            url: 'reacts.php',
            method: 'POST',
            data: {
                video_id: videoId,
                user_id: userId,
                type: type
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    $('#likes-count').text(result.likes);
                    $('#dislikes-count').text(result.dislikes);
                
                    if (result.user_reaction === 'like') {
                        $('.like-btn').addClass('active');
                        $('.dislike-btn').removeClass('active');
                    }
                } else {
                    alert(result.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
    });

    $('.dislike-btn').on('click', function() {
        var videoId = $(this).data('video-id');
        var type = 'dislike'; 
        var userId = <?php echo $_SESSION['user_id']; ?>;

        $.ajax({
            url: 'reacts.php',
            method: 'POST',
            data: {
                video_id: videoId,
                user_id: userId, 
                type: type
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    $('#likes-count').text(result.likes); 
                    $('#dislikes-count').text(result.dislikes);
                
                    if (result.user_reaction === 'dislike') {
                        $('.dislike-btn').addClass('active');
                        $('.like-btn').removeClass('active');
                    }
                } else {
                    alert(result.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
    });
});

    </script>
 <script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.js"></script>
    <script>
        const player = new Plyr('#player', {
    fullscreen: { 
        enabled: true,
        fallback: true,
        iosNative: true 
    }
});

        
    </script>
</body>
</html>
