document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const alertMessage = urlParams.get('alert');
    if (alertMessage) {
        document.getElementById('alertMessage').textContent = alertMessage;
    }
});

document.getElementById('loginForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const userId = document.getElementById('userId').value;
    const password = document.getElementById('password').value;

    const response = await fetch(`../action/login/index.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            userId: userId,
            password: password
        })
    });

    const result = await response.json();
    const storedHashedPassword = result.data;

    if (storedHashedPassword && await bcrypt.compare(password, storedHashedPassword)) {
        const doubleHashedPassword = await bcrypt.hash(storedHashedPassword, 10);
        localStorage.setItem('key', doubleHashedPassword);
        document.cookie = `key=${doubleHashedPassword}; path=/;`;
        window.location.href = '../';
    } else {
        alert('Invalid user ID or password');
    }
});
