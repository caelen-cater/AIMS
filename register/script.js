document.getElementById('signupForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    const password = formData.get('password');
    localStorage.setItem('password', password); // Store password
    const hashedPassword = await bcrypt.hash(password, 10);
    formData.set('password', hashedPassword);

    const response = await fetch('../action/register/index.php', {
        method: 'POST',
        body: formData
    });

    const result = await response.json();
    const userId = result['user id'];
    localStorage.setItem('user', userId); // Store user ID
    window.location.href = `../login/?alert=User ID: ${userId}`;
});
