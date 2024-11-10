const volume = 5; // Define the volume variable here
let containerIdBuffer = '';
let bufferTimeout;
let currentContainerId = '';

document.addEventListener('keypress', function(event) {
    if (event.target.id !== 'itemInput' && event.target.id !== 'containerIdInput' && event.target.id !== 'descriptionInput' && event.target.className !== 'caption-edit') { // Prevent adding Enter key from item input and add entries text box to buffer
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

document.getElementById('containerIdInput').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        const containerId = this.value;
        if (containerId.length === volume) {
            search('container', containerId);
        }
    }
});

document.getElementById('descriptionInput').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        const description = this.value;
        if (description.length > 0) {
            // Handle description input if needed
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    authenticateUser();
});

document.getElementById('addEntryButton').addEventListener('click', function() {
    const addEntryForm = document.getElementById('addEntryForm');
    const containerIdInput = document.getElementById('containerIdInput');
    const overlay = document.getElementById('overlay');
    addEntryForm.style.display = 'block';
    overlay.style.display = 'block';
    if (currentContainerId) {
        containerIdInput.value = currentContainerId;
    }
});

document.getElementById('overlay').addEventListener('click', function() {
    document.getElementById('addEntryForm').style.display = 'none';
    document.getElementById('imageUploadForm').style.display = 'none'; // Close the image upload form
    document.getElementById('overlay').style.display = 'none';
});

document.getElementById('submitEntryButton').addEventListener('click', function() {
    const containerId = document.getElementById('containerIdInput').value;
    const description = document.getElementById('descriptionInput').value;
    const photo = document.getElementById('photoInput').files[0];
    const submitButton = document.getElementById('submitEntryButton');

    if (containerId.length === volume && description.length > 0) {
        const formData = new FormData();
        formData.append('containerId', containerId);
        formData.append('caption', description);
        if (photo) {
            formData.append('image', photo);
        }

        submitButton.textContent = 'Loading...';
        submitButton.classList.add('loading');
        submitButton.disabled = true;

        fetch('action/create/', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            cache: 'no-store' // Prevent caching
        })
        .then(handleApiResponse)
        .then(data => {
            submitButton.textContent = 'Submit';
            submitButton.classList.remove('loading');
            submitButton.disabled = false;

            if (data.success) {
                document.getElementById('addEntryForm').style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
                document.getElementById('containerIdInput').value = '';
                document.getElementById('descriptionInput').value = '';
                document.getElementById('photoInput').value = '';
                search('container', containerId); // Refresh the container view
            } else {
                alert('Error adding entry: ' + data.message);
            }
        })
        .catch(error => {
            submitButton.textContent = 'Submit';
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
            console.error('Error:', error);
        });
    } else {
        alert(`Container ID must be ${volume} characters and description must be at least 1 character`);
    }
});

document.getElementById('submitImageButton').addEventListener('click', function() {
    const imageFileInput = document.getElementById('imageFileInput');
    const file = imageFileInput.files[0];
    const entryId = imageFileInput.getAttribute('data-entry-id');
    const containerId = imageFileInput.getAttribute('data-container-id');
    const submitButton = document.getElementById('submitImageButton');

    if (file) {
        const formData = new FormData();
        formData.append('containerId', containerId);
        formData.append('entryId', entryId);
        formData.append('image', file);

        submitButton.textContent = 'Loading...';
        submitButton.classList.add('loading');
        submitButton.disabled = true;

        fetch('action/edit/', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            cache: 'no-store' // Prevent caching
        })
        .then(handleApiResponse)
        .then(data => {
            submitButton.textContent = 'Submit';
            submitButton.classList.remove('loading');
            submitButton.disabled = false;

            if (data.success) {
                document.getElementById('imageUploadForm').style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
                const imgElement = document.querySelector(`.grid-item[data-entry="${entryId}"] img`);
                imgElement.src = URL.createObjectURL(file);
            } else {
                alert('Error updating image: ' + data.message);
            }
        })
        .catch(error => {
            submitButton.textContent = 'Submit';
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
            console.error('Error:', error);
        });
    } else {
        document.getElementById('imageUploadForm').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }
});

document.getElementById('logoutButton').addEventListener('click', function() {
    window.location.href = 'login/logout';
});

function handleApiResponse(response) {
    if (response.status === 401) {
        window.location.href = 'login/';
        return Promise.reject('Unauthorized');
    }
    return response.json();
}

function authenticateUser() {
    fetch('action/auth/', {
        credentials: 'same-origin',
        cache: 'no-store' // Prevent caching
    })
    .then(handleApiResponse)
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
            'Authorization': 'Basic ' + btoa(user + ':' + password),
            'Cache-Control': 'no-store' // Prevent caching
        }
    })
    .then(handleApiResponse)
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
        if (item.url && item.url.startsWith('http')) { // Ensure the URL is valid
            img.src = item.url;
        } else {
            img.src = 'https://cdn.cirrus.center/static/placeholder.png'; // Use a placeholder image for invalid URLs
        }
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
            'Authorization': 'Basic ' + btoa(user + ':' + password),
            cache: 'no-store' // Prevent caching
        }
    })
    .then(handleApiResponse)
    .then(data => {
        if (data.success) {
            gridItem.remove();
        } else {
            console.error('Error deleting item:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function makeCaptionEditable(captionElement, entryId) {
    const originalText = captionElement.textContent;
    const input = document.createElement('input');
    input.type = 'text';
    input.value = originalText;
    input.className = 'caption-edit';
    captionElement.textContent = '';
    captionElement.appendChild(input);
    input.focus();

    input.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            const newText = input.value;
            if (newText.length > 0) {
                updateCaption(entryId, newText, captionElement);
            } else {
                alert('Caption must be at least 1 character');
            }
        }
    });

    input.addEventListener('blur', function() {
        captionElement.textContent = originalText;
    });
}

function updateCaption(entryId, newText, captionElement) {
    const containerId = captionElement.closest('.grid-item').getAttribute('data-container');
    const formData = new FormData();
    formData.append('containerId', containerId);
    formData.append('entryId', entryId);
    formData.append('caption', newText);

    fetch('action/edit/', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        cache: 'no-store' // Prevent caching
    })
    .then(handleApiResponse)
    .then(data => {
        if (data.success) {
            captionElement.textContent = newText;
        } else {
            alert('Error updating caption: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateImage(entryId, fileInput) {
    const containerId = fileInput.closest('.grid-item').getAttribute('data-container');
    const formData = new FormData();
    formData.append('containerId', containerId);
    formData.append('entryId', entryId);
    formData.append('image', fileInput.files[0]);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'action/edit/', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 401) {
                window.location.href = 'login/';
            } else if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const imgElement = fileInput.closest('.grid-item').querySelector('img');
                    imgElement.src = URL.createObjectURL(fileInput.files[0]);
                } else {
                    alert('Error updating image: ' + response.message);
                }
            } else {
                alert('Error updating image: ' + xhr.statusText);
            }
        }
    };
    xhr.send(formData);
}

document.getElementById('submitImageButton').addEventListener('click', function() {
    const imageFileInput = document.getElementById('imageFileInput');
    const file = imageFileInput.files[0];
    const entryId = imageFileInput.getAttribute('data-entry-id');
    const containerId = imageFileInput.getAttribute('data-container-id');

    if (file) {
        const formData = new FormData();
        formData.append('containerId', containerId);
        formData.append('entryId', entryId);
        formData.append('image', file);

        fetch('action/edit/', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            cache: 'no-store' // Prevent caching
        })
        .then(handleApiResponse)
        .then(data => {
            if (data.success) {
                document.getElementById('imageUploadForm').style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
                const imgElement = document.querySelector(`.grid-item[data-entry="${entryId}"] img`);
                imgElement.src = URL.createObjectURL(file);
            } else {
                alert('Error updating image: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    } else {
        document.getElementById('imageUploadForm').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }
});

function handleDoubleClick(event) {
    const target = event.target;
    const gridItem = target.closest('.grid-item');
    if (!gridItem || gridItem.getAttribute('data-container') !== currentContainerId) {
        return;
    }

    if (target.tagName === 'IMG') {
        const imageUploadForm = document.getElementById('imageUploadForm');
        const overlay = document.getElementById('overlay');
        const imageFileInput = document.getElementById('imageFileInput');
        imageFileInput.setAttribute('data-entry-id', gridItem.getAttribute('data-entry'));
        imageFileInput.setAttribute('data-container-id', gridItem.getAttribute('data-container'));
        imageUploadForm.style.display = 'block';
        overlay.style.display = 'block';
    } else if (target.classList.contains('caption')) {
        makeCaptionEditable(target, gridItem.getAttribute('data-entry'));
    }
}

document.addEventListener('dblclick', handleDoubleClick);