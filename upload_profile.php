<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profile_pic'])) {
    $user_id = $_SESSION['user_id'];
    $imageData = $_POST['profile_pic'];

    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData);

    $newFileName = uniqid('', true) . '.png';
    $fileDestination = 'upload/profile_pics/' . $newFileName;

    if (!is_dir('upload/profile_pics')) {
        mkdir('upload/profile_pics', 0777, true);
    }


    if (file_put_contents($fileDestination, $imageData)) {
        $conn = new mysqli('localhost', 'zhalgas', '2286', 'mydb', 3310);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "UPDATE users SET profile_pic = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $newFileName, $user_id);
        $stmt->execute();

        echo json_encode(['success' => true, 'newImagePath' => $fileDestination]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>
