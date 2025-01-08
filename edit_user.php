<?php
include ('includes/db_connection.php');
$conn = connectDB();
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $id");
    $user = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $phone_number = $_POST['phone_number'];

    $conn->query("UPDATE users SET fname = '$fname', lname = '$lname', email = '$email', dob = '$dob', gender = '$gender', phone_number = '$phone_number' WHERE id = $id");
    header('Location: dashboard.php');
    exit;
}
?>

<form method="post">
    <label>Имя:</label>
    <input type="text" name="fname" value="<?= $user['fname'] ?>" required>
    <br>
    <label>Фамилия:</label>
    <input type="text" name="lname" value="<?= $user['lname'] ?>" required>
    <br>
    <label>Email:</label>
    <input type="email" name="email" value="<?= $user['email'] ?>" required>
    <br>
    <label>Дата рождения:</label>
    <input type="date" name="dob" value="<?= $user['dob'] ?>" required>
    <br>
    <label>Пол:</label>
    <select name="gender">
        <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Мужской</option>
        <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Женский</option>
    </select>
    <br>
    <label>Телефон:</label>
    <input type="text" name="phone_number" value="<?= $user['phone_number'] ?>">
    <br>
    <button type="submit">Сохранить</button>
</form>
