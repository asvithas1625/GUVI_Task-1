$(document).ready(function() {
    $('#registerForm').submit(function(event) {
        event.preventDefault();
        
        var formData = {
            username: $('#username').val(),
            email: $('#email').val(),
            password: $('#password').val()
        };
        
        $.ajax({
            type: 'POST',
            url: 'php/register.php',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                alert('Registration successful. Please login.');
                window.location.href = 'login.html';
            } else if (response.redirect) {
                alert(response.message + ' Redirecting to login page.');
                window.location.href = 'login.html';
            } else {
                alert(response.message);
            }
        })
        .fail(function(error) {
            console.error(error.responseText);
            alert('Registration failed. Please try again.');
        });
    });
});