document.getElementById('signupForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    const response = await fetch('../action/register/index.php', {
        method: 'POST',
        body: formData
    });

    const result = await response.json();
    const userId = result['user id'];
    window.location.href = `../login/?alert=User ID: ${userId}`;
});
