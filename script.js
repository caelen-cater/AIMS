const volume = 5; // Define the volume variable here
let containerIdBuffer = '';
let bufferTimeout;
let currentContainerId = '';

document.addEventListener('keypress', function(event) {
    if (event.target.id !== 'itemInput') { // Prevent adding Enter key from item input to buffer
        containerIdBuffer += event.key;

        clearTimeout(bufferTimeout); // Clear the previous timeout
        bufferTimeout = setTimeout(() => {
            containerIdBuffer = ''; // Reset the buffer if no new digit is typed within the specified time
        }, volume * 1000);

        if (containerIdBuffer.length === volume) {
            currentContainerId = containerIdBuffer;
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

    let url;
    if (type === 'container' && value === 'Enter') {
        url = `action/search/?all=true`;
    } else {
        url = `action/search/?${type}=${value}`;
    }

    fetch(url, {
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
        gridItem.setAttribute('data-container', item.containerId);
        gridItem.setAttribute('data-entry', item.entryId);

        const img = document.createElement('img');
        img.src = item.url;
        gridItem.appendChild(img);

        const caption = document.createElement('div');
        caption.className = 'caption';
        caption.textContent = item.caption;
        gridItem.appendChild(caption);

        // Only add delete button if the currentContainerId matches the item's containerId
        if (currentContainerId === item.containerId) {
            const deleteButton = document.createElement('button');
            deleteButton.className = 'delete-button';
            deleteButton.textContent = 'Delete';
            deleteButton.addEventListener('click', () => deleteItem(index + 1));
            gridItem.appendChild(deleteButton);
        }

        gridContainer.appendChild(gridItem);
    });
}

function deleteItem(entryId) {
    const user = localStorage.getItem('user');
    const password = localStorage.getItem('password');
    const gridItem = document.querySelector(`.grid-item[data-key="${entryId}"]`);
    const containerId = gridItem.getAttribute('data-container');
    const entry = gridItem.getAttribute('data-entry');

    fetch(`action/delete/?container=${containerId}&entry=${entry}`, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Basic ' + btoa(user + ':' + password)
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            gridItem.remove();
        } else {
            console.error('Error deleting item:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
