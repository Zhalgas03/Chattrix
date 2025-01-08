<?php
function streamVideo($filePath) {
    if (!file_exists($filePath)) {
        http_response_code(404);
        die('File not found.');
    }

    $fileSize = filesize($filePath);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    $start = 0;
    $end = $fileSize - 1;

    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = $_SERVER['HTTP_RANGE'];
        if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
            $start = intval($matches[1]);
            if (!empty($matches[2])) {
                $end = intval($matches[2]);
            }
        }
    }

    $end = min($end, $fileSize - 1);
    if ($start > $end || $start >= $fileSize) {
        http_response_code(416);
        header("Content-Range: bytes */$fileSize");
        exit;
    }

    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . ($end - $start + 1));
    header("Content-Range: bytes $start-$end/$fileSize");
    header('Accept-Ranges: bytes');
    http_response_code(206);

    $fp = fopen($filePath, 'rb');
    fseek($fp, $start);
    $chunkSize = 8192;
    $bytesLeft = $end - $start + 1;
    while ($bytesLeft > 0 && !feof($fp)) {
        $read = min($chunkSize, $bytesLeft);
        echo fread($fp, $read);
        flush();
        $bytesLeft -= $read;
    }
    fclose($fp);
    exit;
}

if (isset($_GET['file'])) {
    $filePath = 'upload/' . basename($_GET['file']); 
    streamVideo($filePath);
}
?>
