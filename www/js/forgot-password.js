document.addEventListener('DOMContentLoaded', () => {
    const validateButton = document.getElementById('validateButton');
    const resetButton = document.getElementById('resetButton');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const newPasswordError = document.getElementById('newpasswordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    const togglePasswordIcons = document.querySelectorAll('.eye-icon');

    validateButton.addEventListener('click', () => {
        const emailOrUsername = document.getElementById('emailOrUsername').value;
        const birthday = document.getElementById('birthday').value;
        const gender = document.getElementById('gender').value;

        fetch('server/validate_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ emailOrUsername, birthday, gender })
        })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    alert('User validated successfully!');
                    document.querySelectorAll('#forgotPasswordForm input, #forgotPasswordForm select').forEach(input => {
                        input.disabled = true;
                    });
                    validateButton.style.display = 'none'; // Hide only the validate button
                    document.getElementById('newPassword').disabled = false;
                    document.getElementById('confirmPassword').disabled = false;
                    document.getElementById('resetButton').disabled = false;
                } else {
                    alert('User validation failed. Please check your information.');
                }
            })
            .catch(error => console.error('Error:', error));
    });

    togglePasswordIcons.forEach(eyeIcon => {
        eyeIcon.addEventListener('click', () => {
            const passwordInput = eyeIcon.previousElementSibling;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '&#128065;'; // Eye open icon
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '&#128065;'; // Eye closed icon
            }
        });
    });


    newPassword.addEventListener('input', () => {
        validatePassword();
        checkPasswordsMatch();
    });

    confirmPassword.addEventListener('input', () => {
        validatePassword();
        checkPasswordsMatch();
    });

    resetButton.addEventListener('click', (event) => {
        event.preventDefault(); // Prevent form submission

        const isPasswordValid = validatePassword();
        const doPasswordsMatch = checkPasswordsMatch();

        if (isPasswordValid && doPasswordsMatch) {
            const emailOrUsername = document.getElementById('emailOrUsername').value;
            const password = newPassword.value;

            fetch('server/reset-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ emailOrUsername, newPassword: password })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Password reset successfully!');
                        resetPasswordForm.reset();
                        newPasswordError.textContent = '';
                        confirmPasswordError.textContent = '';
                        window.location.href = '../login.html';

                    } else {
                        alert('Password reset failed. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again later.');
                });
        } else {
            alert('Password must be at least 8 characters, including an uppercase letter, a lowercase letter, a number, and a special symbol.Please make sure the password are match');
        }
    });


    function validatePassword() {
        const password = newPassword.value;
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#]).{8,}$/;
        if (!regex.test(password)) {
            newPasswordError.textContent = 'Password must be at least 8 characters, including an uppercase letter, a lowercase letter, a number, and a special symbol.';
            return false;
        } else {
            newPasswordError.textContent = '';
            return true;
        }
    }

    function checkPasswordsMatch() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPasswordError.textContent = 'Passwords do not match.';
            return false;
        } else {
            confirmPasswordError.textContent = '';
            return true;
        }
    }
});


