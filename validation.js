$(document).ready(function() {
    $('#registerForm').on('submit', function(event) {
        event.preventDefault(); 

        var hasErrors = false;

        $('.error-message').empty();

        $('input').each(function() {
            var field = $(this);
            var value = field.val();
            var s = $("#password-requirements");
            var errorMessage = '';

            if (field.attr('id') === 'email' && value === '') {
                errorMessage = 'Email is required';
            } else if (field.attr('id') === 'email' && !/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zAZ]{2,}$/.test(value)) {
                errorMessage = 'Invalid email format';
            }

            if (field.attr('id') === 'phone-number') {
                if (value === '') {
                    errorMessage = 'Phone number is required';
                } else if (value.length < 5) {
                    errorMessage = 'Phone number is too short';
                } else if (value.length > 11) {
                    errorMessage = 'Phone number is too long';
                } else if (!/^\d+$/.test(value)) {
                    errorMessage = 'Phone number must contain only digits.';
                }
            }

            if (field.attr('id') === 'password') {
                if (value === '') {
                    errorMessage = 'Password is required';
                } else if (value.length < 8) {
                    errorMessage = 'Password must be at least 8 characters';
                } else if (!/[A-Za-z]/.test(value) || !/[0-9]/.test(value) || !/[!@#$%^&*]/.test(value)) {
                    errorMessage = 'Password must contain at least one letter, one number, and one special character';
                }
            }

            if (field.attr('id') === 'dob') {
                if (value === '') {
                    errorMessage = 'Date of birth is required';
                }
            }

            if (field.attr('id') === 'gender') {
                if (value === '') {
                    errorMessage = 'Gender is required';
                }
            }

            if (field.attr('id') === 'confirm-password' && value !== $('#password').val()) {
                errorMessage = 'Passwords do not match';
            }

            if (field.attr('id') === 'check') {
                if (!field.prop('checked')) {
                    errorMessage = '1';
                }
            }

            if (errorMessage) {
                hasErrors = true;
                field.next('.error-message').text(errorMessage);
            }
        });

        if (!hasErrors) {
            var formData = {
                action: 'register',
                email: $('#email').val(),
                password: $('#password').val(),
                fname: $('#fname').val(),
                lname: $('#lname').val(),
                phone_number: $("#country-code").val() + $("#phone-number").val(),
                dob: $("#dob").val(),
                gender: $("#gender").val()
            };

            console.log('Sending data:', formData); 

            $.ajax({
                url: 'test.php', 
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log('Server response:', response); 

                    var jsonResponse = JSON.parse(response);

                    if (jsonResponse.status === "success") {
                        alert(jsonResponse.message);
                        window.location.href = 'login.php';
                    } else if (jsonResponse.status === "error") {
                        for (var field in jsonResponse.errors) {
                            $("#" + field).next('.error-message').text(jsonResponse.errors[field]);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    alert('An error occurred. Please try again later.');
                }
            });
        }
    });
});
