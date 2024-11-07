const volume = 5; // Define the volume variable here
let containerIdBuffer = '';
let bufferTimeout;

document.addEventListener('keypress', function(event) {
    if (event.target.id !== 'itemInput') { // Prevent adding Enter key from item input to buffer
        containerIdBuffer += event.key;

        clearTimeout(bufferTimeout); // Clear the previous timeout
        bufferTimeout = setTimeout(() => {
            containerIdBuffer = ''; // Reset the buffer if no new digit is typed within the specified time
        }, volume * 1000);

        if (containerIdBuffer.length === volume) {
            search('container', containerIdBuffer);
            containerIdBuffer = ''; // Reset the buffer after search
        }
    }
});

document.getElementById('itemInput').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        const item = this.value;
        if (item.length > 0) {
            search('item', item);
            containerIdBuffer = ''; // Reset the buffer to ensure container search works after item search
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    authenticateUser();
});

function authenticateUser() {
    fetch('action/auth/', {
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.status === 401) {
            window.location.href = 'login';
        }
    })
    .catch(error => console.error('Error:', error));
}

function search(type, value) {
    const user = localStorage.getItem('user');
    const password = localStorage.getItem('password');

    fetch(`action/search/?${type}=${value}`, {
        headers: {
            'Authorization': 'Basic ' + btoa(user + ':' + password)
        }
    })
    .then(response => response.json())
    .then(data => displayGrid(data.data))
    .catch(error => console.error('Error:', error));
}

function displayGrid(data) {
    const gridContainer = document.getElementById('gridContainer');
    gridContainer.innerHTML = '';

    data.forEach((item, index) => {
        const gridItem = document.createElement('div');
        gridItem.className = 'grid-item';
        gridItem.setAttribute('data-key', index + 1);

        const img = document.createElement('img');
        img.src = item.url;
        gridItem.appendChild(img);

        const caption = document.createElement('div');
        caption.className = 'caption';
        caption.textContent = item.caption;
        gridItem.appendChild(caption);

        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete-button';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => deleteItem(index + 1));
        gridItem.appendChild(deleteButton);

        gridContainer.appendChild(gridItem);
    });
}

function deleteItem(key) {
    const user = localStorage.getItem('user');
    const password = localStorage.getItem('password');

    fetch(`action/delete/?key=${key}`, {
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
