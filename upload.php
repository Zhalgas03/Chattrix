<?php
session_start();

// Проверка, чтобы пользователь был авторизован
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'zhalgas', '2286', 'mydb', 3310);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обработка загрузки файла
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    $allowedExtensions = ['mp4', 'avi', 'mkv'];
    $filename = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

    // Получаем название видео из формы
    $videoTitle = isset($_POST['video_title']) ? $_POST['video_title'] : 'Untitled';

    // Преобразуем название для имени файла (заменяем пробелы и недопустимые символы)
    $sanitizedTitle = strtolower(trim(preg_replace('/[^A-Za-z0-9\-]/', '-', $videoTitle), '-'));

    if (in_array(strtolower($fileExt), $allowedExtensions)) {
        $uploadDir = "upload/"; // Папка для загрузки
        $newFilename = $sanitizedTitle . "." . $fileExt; // Используем преобразованное название как имя файла
        $location = $uploadDir . $newFilename;

        // Проверяем права на запись в папку upload
        if (!is_writable($uploadDir)) {
            die("Upload directory is not writable. Please check directory permissions.");
        }

        // Перемещаем файл в папку upload
        if (move_uploaded_file($fileTmpName, $location)) {
            // Генерация slug
            $slugBase = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $videoTitle), '-'));
            $slug = $slugBase . '-' . uniqid();

            // Генерация обложки (thumbnail) с помощью FFmpeg
            $thumbnailPath = "upload/thumbnails/" . $sanitizedTitle . "-thumbnail.jpg";  // Добавляем суффикс для обложки
            $command = "ffmpeg -i $location -vf \"thumbnail,scale=320:240\" -frames:v 1 $thumbnailPath";
            exec($command); // Запускаем команду для генерации thumbnail

            // Сохраняем информацию о видео в базе данных, но без пути к thumbnail
            $stmt = $conn->prepare("INSERT INTO videos (user_id, filename, title, slug) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $_SESSION['user_id'], $newFilename, $videoTitle, $slug);
            $stmt->execute();
            $stmt->close();

            echo "Video uploaded successfully with title: " . htmlspecialchars($videoTitle);
        } else {
            echo "Failed to upload video. Error: " . $_FILES['file']['error'];
        }
    } else {
        echo "Invalid file format. Only MP4, AVI, and MKV files are allowed.";
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video</title>
    <style>
        video {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Upload Video</h1>

    <form id="uploadForm" enctype="multipart/form-data" method="POST" action="upload.php">
        <label for="file">Select Video:</label>
        <input id="file" type="file" name="file" accept=".mp4,.avi,.mkv" required />
        <br><br>

        <div id="videoPreview" style="display:none;">
            <h3>Preview:</h3>
            <video id="videoPreviewPlayer" width="320" height="240" controls>
                <source id="previewSource" src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <br><br>

        <label for="video_title">Video Title:</label>
        <input type="text" id="video_title" name="video_title" placeholder="Enter video title" required />
        <br><br>

        <button type="submit">Upload Video</button>
    </form>

    <a href="profile.php">Back to Profile</a>
    <a href="index.php">Back to Gallery</a>
    <script>
        document.getElementById('file').addEventListener('change', function(event) {
            var file = event.target.files[0];
            var videoPreview = document.getElementById('videoPreview');
            var videoPlayer = document.getElementById('videoPreviewPlayer');
            var previewSource = document.getElementById('previewSource');

            if (file) {
                var videoUrl = URL.createObjectURL(file);
                previewSource.src = videoUrl;
                videoPlayer.load();

                // Показываем блок с предпросмотром
                videoPreview.style.display = 'block';
            }
        });
    </script>
</body>
</html>
