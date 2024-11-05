<?php
// Read the user ID and password from the POST request
$userId = $_POST['userId'];
$password = $_POST['password'];

// Retrieve the API key from the configuration file
require_once '../../config.php';
$key = $cirrusapi;

// Initialize cURL session
$ch = curl_init("https://api.cirrus.center/v2/data/database/?db=AIMS&log=0&entry=$userId");

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $key,
    'Content-Type: application/x-www-form-urlencoded'
]);

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    http_response_code(500);
    die('Curl error: ' . curl_error($ch));
}

// Close cURL session
curl_close($ch);

// Decode the response
$responseData = json_decode($response, true);
$storedHashedPassword = $responseData['data'];

// Validate the password
if (password_verify($password, $storedHashedPassword)) {
    http_response_code(200);
} else {
    http_response_code(429);
}
?>
