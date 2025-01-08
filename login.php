
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="show.js"></script>
    <link rel="stylesheet" href="style.css"> 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="logval.js"></script>
</head>
<body>


   
<div class="main">
    <form id="loginForm" method="POST" class="form ">
    <input type="hidden" name="action" value="login">
        <h1 class="text-white text-center ">Log in</h1>
        <p class="text-white text-center " style="margin-top:8px; font-size:15px">Don't have a Chattrix account? <a class="forgot" href="register.php">Sign up</a></p>
        <div class="form-group mb-2  mt-5">
            <label for="exampleInputEmail1" class="text-white " style="position:relative; top:-6px;">Email address</label>
            <input type="email" class="form-control" id="email" name="email" id="exampleInputEmail1">
        </div>
        <div class="form-group my-2">
            <label for="pass" class="text-white" style="position:relative; top:8px;">Password</label>
            <small class="form-text text-white" style="float: right;">
                <button type="button" class="showbtn btn btn-link text-white">
                    <i id="eye-icon" class="fas fa-eye"></i> Show
                </button>
            </small>
            <input type="password" id="pass" class="form-control password-field" name="password" class="form-control">
            <small class="form-text text-white" id="password" style="float: right;"><a class="forgot" href="#">Forgot your password?</a></small>
         </div>
         <div class="form-check my-2">
            <input type="checkbox" class="form-check-input" id="check">
            <label class="form-check-label text-white" for="check">Remember me</label>
        </div>
      <br>
      <button type="submit" class="btn text-white w-100">Log in</button>
      <div id="errorMessage" style="color: red;"></div>
    </form>
</div>
</script>

</script>
</body>
</html>
