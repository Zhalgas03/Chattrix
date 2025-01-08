$(document).ready(function () {
    $("#loginForm").submit(function (e) {
        e.preventDefault();

        var email = $("#email").val();
        var password = $("#pass").val();
        var errorMessage = "";

        if (email === "") {
            errorMessage = "Email cannot be empty";
        } else if (password === "") {
            errorMessage = "Password cannot be empty";
        }

        if (errorMessage !== "") {
            $("#errorMessage").text(errorMessage).show();
        } else {
            $.ajax({
                url: "login_check.php",
                type: "POST",
                data: {
                    email: email,
                    password: password
                },
                success: function (response) {
                    var data = JSON.parse(response);

                    if (data.status === "success") {
                        window.location.href = "profile.php?slug=" + data.profile_slug; 
                    } else {
                        $("#errorMessage").text(data.message).show();
                    }
                },
                error: function () {
                    $("#errorMessage").text("Error occurred. Try again later.").show();
                }
            });
        }
    });
});
