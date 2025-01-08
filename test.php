<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? htmlspecialchars($_POST['action']) : '';
    
    if ($action === "register") {
        $email = $fname = $lname = $phone_number = $dob = $gender = '';
        $email_err = $fname_err = $lname_err = $phone_err = $dob_err = $gender_err = $password_err = '';

      
        if (empty($_POST['email'])) {
            $email_err = "Email is empty.";
        } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $email_err = "Invalid email format.";
        } else {
            $email = htmlspecialchars(trim($_POST['email']));
        }



      
        if (empty($_POST['password'])) {
            $password_err = "Password is empty.";
        } else if (strlen($_POST['password']) < 8) {
            $password_err = "Password must be at least 8 characters.";
        } else {
            $password = password_hash(htmlspecialchars(trim($_POST['password'])), PASSWORD_DEFAULT);
        }

   
        if (empty($_POST['fname'])) {
            $fname_err = "First name is empty.";
        } else {
            $fname = htmlspecialchars(trim($_POST['fname']));
        }

  
        if (empty($_POST['lname'])) {
            $lname_err = "Last name is empty.";
        } else {
            $lname = htmlspecialchars(trim($_POST['lname']));
        }

 
        if (empty($_POST['phone_number'])) {
            $phone_err = "Phone number is required.";
        } elseif (!preg_match('/^\+?\d{1,4}[\s-]?\(?\d+\)?[\s-]?\d{1,4}[\s-]?\d+$/', $_POST['phone_number'])) {
            $phone_err = "Invalid phone number format.";
        } else {
            $phone_number = htmlspecialchars(trim($_POST['phone_number']));
        }

  
        if (empty($_POST['dob'])) {
            $dob_err = "Date of birth is required.";
        } else {
            $dob = htmlspecialchars(trim($_POST['dob']));
        }


        $gender = isset($_POST['gender']) ? $_POST['gender'] : NULL;
        if ($gender === NULL) {
            $gender_err = "Gender is required.";
        }

        if (empty($email_err)) {
            $conn = new mysqli('localhost', 'zhalgas', '2286', 'mydb', 3310);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $email_err = "This email is already registered.";
            }
        }

   

        $profile_slug = strtolower($fname . '-' . $lname . '-' . uniqid());
        
        if (empty($email_err) && empty($fname_err) && empty($lname_err) && empty($phone_err) && empty($dob_err) && empty($gender_err) && empty($password_err)) {
            $conn = new mysqli('localhost', 'zhalgas', '2286', 'mydb', 3310);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "INSERT INTO users (fname, lname, email, password, dob, gender, phone_number,profile_slug) VALUES (?,?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $fname, $lname, $email, $password, $dob, $gender, $phone_number, $profile_slug);
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Registration Successful!",
                    "profile_slug" => $profile_slug
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Database error: " . $stmt->error
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "errors" => [
                    "email" => $email_err,
                    "password" => $password_err,
                    "fname" => $fname_err,
                    "lname" => $lname_err,
                    "gender" => $gender_err,
                    "dob" => $dob_err,
                    "phone_number" => $phone_err
                ]
            ]);
        }
    }
}
?>
