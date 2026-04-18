// Form submission with server-side handling
document.getElementById('loginForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission
    let isValid = true;

    // Clear previous error messages
    document.querySelectorAll('.error-message').forEach(msg => msg.style.display = 'none');

    // Validate email
    const email = document.getElementById('email').value;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        document.getElementById('emailError').innerText = 'Please enter a valid email address.';
        document.getElementById('emailError').style.display = 'block';
        isValid = false;
    }

    // Validate password
    const password = document.getElementById('password').value;
    if (password.length < 8) {
        document.getElementById('passwordError').innerText = 'Password must be at least 8 characters.';
        document.getElementById('passwordError').style.display = 'block';
        isValid = false;
    }

    // If the form is valid, send data to the server
    if (isValid) {
        fetch('server/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        })
            .then(response => response.text())
            .then(data => {
                if (data.includes('Login successful')) {
                    alert('Login successful!');
                    window.location.href = "../profile.html"; // Redirect to journal page on success
                } else {
                    alert(data); // Show error message from the server
                }
            })
            .catch(error => {
                alert('Something went wrong. Please try again later.');
                console.error('Error:', error);
            });
    }
});

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordField = document.getElementById('password');
    const type = passwordField.type === 'password' ? 'text' : 'password';
    passwordField.type = type;
    this.textContent = passwordField.type === 'password' ? '\u{1F441}' : '\u{1F441}\uFE0E'; // Change icon
});

// Real-time validation for email
document.getElementById('email').addEventListener('input', function () {
    const email = this.value;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        document.getElementById('emailError').innerText = 'Please enter a valid email address.';
        document.getElementById('emailError').style.display = 'block';
    } else {
        document.getElementById('emailError').style.display = 'none';
    }
});

// Real-time validation for password
document.getElementById('password').addEventListener('input', function () {
    const password = this.value;
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
    if (!passwordPattern.test(password)) {
        document.getElementById('passwordError').innerText = 'Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, a number, and a special symbol.';
        document.getElementById('passwordError').style.display = 'block';
    } else {
        document.getElementById('passwordError').style.display = 'none';
    }
});
