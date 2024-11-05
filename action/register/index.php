<?php
// Read the password from the POST request
$password = $_POST['password'];

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Retrieve the API key from the configuration file
require_once '../../config.php';
$key = $cirrusapi;

// Prepare the data to be sent
$data = [
    'db' => 'AIMS',
    'log' => '0',
    'value' => $hashedPassword
];

// Initialize cURL session
$ch = curl_init('https://api.cirrus.center/v2/data/database/');

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $key,
    'Content-Type: application/x-www-form-urlencoded'
]);

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    die('Curl error: ' . curl_error($ch));
}

// Close cURL session
curl_close($ch);

// Decode the response
$responseData = json_decode($response, true);

// Output the 'entry' value as 'user id' in JSON format
header('Content-Type: application/json');
echo json_encode(['user id' => $responseData['entry']]);
?>