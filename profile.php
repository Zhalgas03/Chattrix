<?php
session_start();

$conn = new mysqli('localhost', 'zhalgas', '2286', 'mydb', 3310);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$profileLink = ''; 
$profilePic = 'default_photo.jpg';
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);

$is_own_profile = ($profile_id === $_SESSION['user_id']);


$sql = "SELECT id, fname, lname, profile_pic, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


if (!$user) {
    die("User not found.");
}


$current_user_id = $_SESSION['user_id'];
$profilePic = 'default_photo.jpg'; 

if (isset($current_user_id)) {
    $sql = "SELECT profile_pic, fname, lname, email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_user = $result->fetch_assoc();

    $profilePic = $current_user['profile_pic'] ?: 'default_photo.jpg';
    $profileLink = 'profile.php?id=' . $current_user_id;
}

$sql = "SELECT v.id, v.filename, v.slug, v.title, v.user_id, v.views, v.upload_date, u.fname, u.profile_pic 
        FROM videos v
        JOIN users u ON v.user_id = u.id
        WHERE v.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();

$videos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
}


$sql = "SELECT COUNT(*) as followers_count FROM follows WHERE channel_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$followers = $result->fetch_assoc();
$followers_count = $followers['followers_count'];




$fname = isset($user['fname']) ? htmlspecialchars($user['fname']) : 'Unknown';
$lname = isset($user['lname']) ? htmlspecialchars($user['lname']) : 'Unknown';
$email = isset($user['email']) ? htmlspecialchars($user['email']) : 'Unknown';



$is_following = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $follow_sql = "SELECT * FROM follows WHERE user_id = ? AND channel_id = ?";
    $follow_stmt = $conn->prepare($follow_sql);
    $follow_stmt->bind_param("ii", $user_id, $profile_id);
    $follow_stmt->execute();
    $follow_result = $follow_stmt->get_result();
    $is_following = $follow_result->num_rows > 0;
    $follow_stmt->close();
}


$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?>'s Profile</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="mob.js"></script>
</head>
<body class="profbody">
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
                    <h5><?php echo htmlspecialchars($current_user['fname'])." ".htmlspecialchars($current_user['lname']) ?></h5>
                    <p class="dmail"><?php echo htmlspecialchars($current_user['email'])?></p>
                </div>
            </a></li>
            <li><a class="dropdown-item" href="<?php echo $profileLink; ?>"><i id="logicon" class="fa-solid fa-user"></i> Open Profile</a></li>
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
    <div class="profile-container">
   


        <div class="prof1">
            <div class="profpic">
                <div class="profile-pic-container">
                    <img src="upload/profile_pics/<?php echo htmlspecialchars($user['profile_pic'] ?: 'default_photo.jpg'); ?>"
                        alt="Profile Picture"
                        class="profile-pic"
                        id="profile-pic"
                        data-bs-toggle="modal"
                        data-bs-target="#profileModal">
                    <div class="camera-icon-container">
                        <i class="fas fa-camera camera-icon"></i>
                    </div>
                </div>
            </div>
            <div class="proftext">
                <h2 class="proftext1"><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></h2>
                <h4 class="uname"><?php echo htmlspecialchars("@".$user['fname'] . $user['id']." • ".$followers_count." followers"." • ".count($videos)." videos"); ?></h4>
                <div class="bbuttons">
                <div class="bb1">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <button id="follow-btn2" class="<?php echo $is_following ? 'subscribed' : ''; ?>" onclick="toggleFollow(<?php echo $profile_id; ?>, '<?php echo $is_following ? 'unfollow' : 'follow'; ?>')">
                                <?php echo $is_following ? 'Unfollow' : 'Follow'; ?>
                            </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <p><a href="/login.php">Log in</a> to follow this channel.</p>
                        <?php endif; ?>
                </div>
                <div>
                    
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="chat.php?receiver_id=<?= $user['id'] ?>"><button class="bb2">Message</button></a>
                    <?php endif; ?>
                    <?php if ($is_own_profile): ?>
                        <div class="upload-section">
                            <a  href="beta.php">
                                <button class="bb3">Upload Video</button>
                        </a>
                     </div>
        <?php endif; ?>
                </div>
            </div>
            </div>
            
            
        </div>


<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profileModalLabel">Change Profile Picture</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <form id="uploadForm" action="upload_profile.php" method="POST" enctype="multipart/form-data">
          <input type="file" id="uploadImage" accept="image/*" />
          <div id="file-name" class="mt-2 text-muted">No file chosen</div>
          <br />
          <img id="image" style="max-width: 100%; display: none;" />
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
        <button type="button" id="uploadButton" class="btn btn-primary">Upload</button>
      </div>
    </div>
  </div>
</div>





        <h2 class="pvt"><?php echo $is_own_profile ? "Your Videos" : htmlspecialchars($user['fname']) . "'s Videos"; ?></h2>
        <div id="wg" class="video-gallery">
    <?php if (count($videos) > 0): ?>
        <?php foreach ($videos as $video): ?>
            <div style="margin:5px 0" class="video-item">
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
                        <a class="vt" href="video.php?slug=<?php echo htmlspecialchars($video['slug']); ?>">
                            <p class="vtitle"><strong><?php echo htmlspecialchars($video['title']); ?></strong></p>
                        </a>
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
        <p><?php echo $is_own_profile ? "You have not uploaded any videos yet." : "This user has not uploaded any videos yet."; ?></p>
    <?php endif; ?>
                <div class="footer"> </div>
</div>




    </div>
    
<script>
document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("uploadImage");
    const fileNameDisplay = document.getElementById("file-name");
    const uploadButton = document.getElementById("uploadButton");
    const imagePreview = document.getElementById("image");
    let cropper;

    // Обработка выбора файла
    fileInput.addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (file) {
            fileNameDisplay.textContent = file.name; 
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';


                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(imagePreview, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true
                });
            };
            reader.readAsDataURL(file);
        }
    });


uploadButton.addEventListener('click', function() {
    if (cropper) {
        const croppedCanvas = cropper.getCroppedCanvas();
        const croppedImage = croppedCanvas.toDataURL('image/png');

        const formData = new FormData();
        formData.append('profile_pic', croppedImage);

        fetch('upload_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error uploading image.');
            }
        })
        .catch(error => {
            alert('Error uploading image: ' + error);
        });
    } else {
        alert("Please crop the image before uploading.");
    }
});

});

</script>
</body>
</html>