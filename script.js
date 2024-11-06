const volume = 10; // Define the volume variable here

document.getElementById('containerIdInput').addEventListener('input', function() {
    const containerId = this.value;

    if (containerId.length === volume) {
        const user = localStorage.getItem('user');
        const password = localStorage.getItem('password');

        fetch(`action/search/index.php?containerId=${containerId}`, {
            headers: {
                'Authorization': 'Basic ' + btoa(user + ':' + password)
            }
        })
        .then(response => response.json())
        .then(data => displayGrid(data.data))
        .catch(error => console.error('Error:', error));
    }
});

function displayGrid(data) {
    const gridContainer = document.getElementById('gridContainer');
    gridContainer.innerHTML = '';

    for (const key in data) {
        const item = data[key];
        const gridItem = document.createElement('div');
        gridItem.className = 'grid-item';

        const img = document.createElement('img');
        img.src = item[1]; // Assuming URL is at index 1
        gridItem.appendChild(img);

        const caption = document.createElement('div');
        caption.className = 'caption';
        caption.textContent = item[2]; // Assuming caption is at index 2
        gridItem.appendChild(caption);

        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete-button';
        deleteButton.textContent = 'X';
        deleteButton.addEventListener('click', () => deleteItem(key));
        gridItem.appendChild(deleteButton);

        gridContainer.appendChild(gridItem);
    }
}

function deleteItem(key) {
    const user = localStorage.getItem('user');
    const password = localStorage.getItem('password');

    fetch(`action/delete/index.php?key=${key}`, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Basic ' + btoa(user + ':' + password)
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`.grid-item[data-key="${key}"]`).remove();
        } else {
            console.error('Error deleting item:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
