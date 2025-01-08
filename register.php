
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="show.js"></script>
    <script src="validation.js"></script>
    <link rel="stylesheet" href="style.css"> 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="rbody">


   
<div class="main">
    
    <form method="POST" id="registerForm" class="form reg">
    <input type="hidden" name="action" value="register">
        <h1 id="regt" class="text-white text-center ">Create an account</h1>
        <p class="text-white text-center " style="margin-top:8px; font-size:15px">Already have a Chattrix account? <a class="forgot" href="login.php">Sign up</a></p>
    
        
        <div class="form-group mb-3 mt-5">
        <div class="row">
            <div class="col-md-6">
                <label for="fname" class="text-white" >First name</label>
                <input type="text" class="form-control" name="fname" id="fname">
                <div class="error-message text-danger"></div>
            </div>
            <div class="col-md-6">
            <label for="lname" class="text-white">Last name</label>
                <input type="text" class="form-control" name="lname" id="lname">
                <div class="error-message text-danger"></div>
                
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <div class="row">
            <div class="col">
                <label for="email" class="text-white ">Email address</label>
                <input type="email" class="form-control" name="email" id="email">
                <div class="error-message text-danger"></div>
            </div>

        </div>
    </div>


    <div class="form-group mb-3">
    <div class="row">
        
        <div class="col-4 col-md-3">
            <label class="text-white" for="country-code">Country Code</label>
            <select class="form-select" id="country-code" name="country_code" required>
                <option value="" disabled selected>+</option>
                <option value="+44">+39 (IT)</option>
                <option value="+1">+1 (USA)</option>
                <option value="+44">+44 (UK)</option>
                <option value="+7">+7 (KZ)</option>
                <option value="+86">+86 (CN)</option>
        
            </select>
        </div>

        
        <div class="col-8 col-md-9">
            <label class="text-white" for="phone-number">Phone Number</label>
            <input type="tel" id="phone-number" name="phone_number" class="form-control" required>
            <div class="error-message text-danger"></div>
        </div>
    </div>
</div>

    <div class="form-group mb-3">
    <div class="row">
  
        <div class="col-6 col-md-6">
            <label class="text-white" for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" class="form-control" required>
        </div>

        <div class="col-6 col-md-6">
            <label class="text-white" for="gender">Gender</label>
            <select class="form-select " id="gender" name="gender" required>
                <option value="" disabled selected>Your gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>
    </div>
</div>





        <div class="form-group my-2">
            <div class="row">
                <div class="col-md-6">
                    <label for="password" class="text-white">Password</label>
                
                    <input type="password" id="password" class="form-control password-field" name="password">
                    <div class="error-message text-danger"></div>
                </div>
                <div class="col-md-6">
                    <label for="confirm-password" id="confirm" class="text-white">Confirm your password</label>
                    
                    <input type="password" id="confirm-password" class="form-control password-field">
                    <div class="error-message text-danger"></div>
                </div>
            </div>
            <small class="form-text text-white" style="float: right;">
                    <button type="button" class="showbtn btn btn-link text-white">
                        <i id="eye-icon" class="fas fa-eye"></i> Show
                    </button>
                </small>
                <small class="form-text" id="password-requirements">Use 8 or more characters with a mix of letters,numbers & symbols</small>
        </div>
     
        <div class="form-check  d-flex align-items-start">
            <input type="checkbox" class="form-check-input me-2" id="check">
            <label class="form-check-label text-white" for="check">
                By creating an account, you agree to our 
                <a class="forgot" href="#">Terms of use</a> and 
                <a class="forgot" href="#">Privacy Policy</a>
            </label>
            <div class="error-message text-danger"></div>
        </div>
    
        
      <br>
      <button type="submit" class="btn text-white w-100">Create an account</button>
    </form>
</div>

</body>
</html>
