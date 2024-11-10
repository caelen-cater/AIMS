# AIMS
An Inventory Management System

## Installation Guide

1. **Clone the repository:**
 ```bash
git clone https://github.com/caelen-cater/AIMS
```

2. **Install dependencies:**
   Ensure you have PHP and a web server (like Apache or Nginx) installed.

3. **Configure API keys:**
   Update the API keys in the following files:
   - config.php:
     $apikey = 'API_KEY';
   - login/auth/index.php:
     $apikey = 'API_KEY';
   - login/auth/token/index.php:
     $apikey = 'API_KEY';
   - login/logout/index.php:
     $apikey = 'API_KEY';

Learn about API keys [here](https://api.cirrus.center/docs/hc/articles/3/14/12/api-keys)

4. **Start the server:**
   Configure your web server to serve the project directory.

## Features

- **Add Entries:**
  - Click the "Add Entry" button to create a new entry.

- **Edit Entries:**
  - Double-click on a caption to edit it.
  - Double-click on an image to upload a new one.

- **Search Functionality:**
  - Search for containers by typing directly on the page (designed for barcode scanners).
  - Search for items to see which container they are in.

## Usage

### Editing Entries

- **Edit Caption:**
  Double-click on the caption of an entry to make it editable. After editing, press `Enter` to save the changes.

- **Edit Image:**
  Double-click on the image of an entry to open the image upload form. Select a new image and click `Submit` to update the image.

### Searching

- **Search Containers:**
  Type the container ID directly on the page to search for a container. The system is designed to work with barcode scanners, so you can scan a barcode to search.

- **Search Items:**
  Use the search functionality to find items and see which container they are in.

- **Seach Everything:**
  Search 'index' to display every AIMS entry

## API Keys

Ensure you have set your API keys in the following files:
- config.php
- login/auth/index.php
- login/auth/token/index.php
- login/logout/index.php
