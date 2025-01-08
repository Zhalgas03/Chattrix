<?php
include ('includes/db_connection.php');
$conn = connectDB();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM users WHERE id = $id");
    header('Location: dashboard.php');
    exit;
}
?>
