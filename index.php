<?php
session_start(); 
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
$liked = isset($_GET['liked']) && $_GET['liked'] == 'true';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$following_only = isset($_GET['following']) && $_GET['following'] == 'true';
$sql = "SELECT 
            videos.id, 
            videos.filename, 
            videos.slug, 
            videos.upload_date, 
            videos.user_id, 
            videos.views, 
            videos.title,
            users.profile_pic,
            users.fname,
            CONCAT(users.fname, videos.user_id) AS nick
        FROM videos 
        LEFT JOIN users ON videos.user_id = users.id";

if ($following_only) {
    $sql .= " WHERE videos.user_id IN (SELECT channel_id FROM follows WHERE user_id = ?)";
} 
elseif ($liked) {
    $sql .= " LEFT JOIN video_likes ON videos.id = video_likes.video_id 
              WHERE video_likes.user_id = ? AND video_likes.type = 'like'";
}
elseif ($search) {
    $sql .= " WHERE (videos.filename LIKE ? OR CONCAT(users.fname, videos.user_id) LIKE ?)";
}

$stmt = $conn->prepare($sql);
if ($following_only || $liked) {
    $stmt->bind_param("i", $_SESSION['user_id']);
} elseif ($search) {
    $searchTerm = '%' . $search . '%';
    $stmt->bind_param("ss", $searchTerm,$searchTerm);
}
$stmt->execute();
$result = $stmt->get_result();

$videos = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
}

$conn->close();

function displayTitle($slug) {
    return ucwords(str_replace('-', ' ', $slug));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Gallery</title>
    <link rel="stylesheet" href="style2.css">
    <script src="mob.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="main">
    <div class="vmain">

    
    <nav class="navbar navbar-dark fixed-top">
      <div class="ncontent">
          <a class="navbar-brand" href="index.php">
            <img src="logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
            Chattrix
          </a>
   
          <div class="navigation">
            <a class="icon" href="index.php"><i class="fa-solid fa-house"></i></a>
            <a class="icon" href="index.php?following=true"><i class="fa-solid fa-user-group"></i></a>
            <a class="icon" href="index.php?liked=true"><i class="fa-solid fa-heart"></i></a>
            <a class="icon" href="chat.php?receiver_id=45"><i class="fa-solid fa-envelope"></i></a>
            
          </div>
          <form class="form-inline" id="search" action="index.php" method="GET">
            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" id="searchbar" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button id="sb" class="btn btn-outline-success my-2 my-sm-0" type="submit"><i id="sb1" class="fa-solid fa-magnifying-glass"></i></button>
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
    <a href="<?php echo $profileLink; ?>" id="profileLink" class="d-none">Go to Profile</a>
<?php else: ?>
    <a href="login.php" class="btn btn-outline-light my-2 my-sm-0 ml-3">Login</a>
<?php endif; ?>



        </div>
      </div>
    </nav>
    

    <div class="video-gallery">
        <?php if (count($videos) > 0): ?>
            <?php foreach ($videos as $video): ?>
                <div class="video-item">
                    <div class="video">
                        <a href="video.php?slug=<?php echo htmlspecialchars($video['slug']); ?>">
                            <?php
                            $thumbnailFile = pathinfo($video['filename'], PATHINFO_FILENAME) . '-thumbnail.jpg';
                            $thumbnailPath = 'upload/thumbnails/' . $thumbnailFile;
                            if (!file_exists($thumbnailPath)) {
                                $thumbnailPath = 'upload/thumbnails/default.jpg';
                            }
                            ?>
                        
                            <img class="oblozhka" src="<?php echo $thumbnailPath; ?>" width="320" height="180" alt="Thumbnail">
                        </a>
                    </div>
                    <div class="uvideo">
                        <div class="user-infos">
                            <a href="profile.php?id=<?php echo htmlspecialchars($video['user_id']); ?>">
                                <img class="img" src="upload/profile_pics/<?php echo htmlspecialchars($video['profile_pic']); ?>" alt="Profile Picture" width="35" height="35">
                            </a>
                        </div>
                        <div class="uvideo_text">
                            <a class="vt" href="video.php?slug=<?php echo htmlspecialchars($video['slug']); ?>"><p class="vtitle"><strong><?php echo htmlspecialchars($video['title']); ?></strong></p> </a>
                            <a class="vid" href="video.php?slug=<?php echo htmlspecialchars($video['slug']); ?>"> <?php echo htmlspecialchars($video['fname']).htmlspecialchars($video['user_id']); ?></a>
                            <div class="vandd">
                                <p><?php echo htmlspecialchars($video['views']); ?> views</p>
                                <p><?php echo date('F j, Y', strtotime($video['upload_date'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No videos found.</p>
        <?php endif; ?>
    </div>
            <div class="footer"> </div>
    </div>


</body>
</html>
