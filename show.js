$(document).ready(function() {
    $('.showbtn').click(function(event){
        event.preventDefault();


        $('.password-field').each(function() {
            var passfield = $(this);
            if (passfield.attr('type') === 'password') {
                passfield.attr('type', 'text');
            } else {
                passfield.attr('type', 'password');
            }
        });


        var eyeIcon = $('#eye-icon');
        if (eyeIcon.hasClass('fa-eye')) {
            eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
});

