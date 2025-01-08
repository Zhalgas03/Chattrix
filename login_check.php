<?php
session_start(); 

include 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '';

    $conn = new mysqli('localhost', 'zhalgas', '2286', 'mydb', 3310);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id, fname, lname, email, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_name'] = $user['fname'];
            $_SESSION['user_id'] = $user['id'];
            $profile_slug = strtolower($user['fname'] . '-' . $user['lname'] . '-' . uniqid());

            $_SESSION['profile_slug'] = $profile_slug;

            echo json_encode([
                "status" => "success",
                "profile_slug" => $profile_slug
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect password."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No user found with that email."]);
    }

    $stmt->close();
    $conn->close();
}
?>
