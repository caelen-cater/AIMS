document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const alertMessage = urlParams.get('alert');
    if (alertMessage) {
        const alertDiv = document.getElementById('alertMessage');
        alertDiv.textContent = alertMessage;
        alertDiv.style.display = 'block';
    }
});

document.getElementById('loginForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const userId = document.getElementById('userId').value;
    const password = document.getElementById('password').value;

    const response = await fetch('../action/login/index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            userId: userId,
            password: password
        })
    });

    if (response.status === 200) {
        localStorage.setItem('user', userId); // Store user ID
        localStorage.setItem('password', password); // Store password
        window.location.href = '../';
    } else if (response.status === 429) {
        alert('Invalid user ID or password');
    } else {
        alert('An error occurred. Please try again.');
    }
});
