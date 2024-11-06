<?php
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

list($type, $data) = explode(' ', $headers['Authorization']);
list($user, $password) = explode(':', base64_decode($data));

// Authenticate user
$loginUrl = "https://api.cirrus.center/v2/login";
$loginResponse = file_get_contents($loginUrl, false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode(['user' => $user, 'password' => $password])
    ]
]));

$loginData = json_decode($loginResponse, true);
if (!$loginData['success']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$key = $_GET['key'];
// Implement delete functionality
// Example: $deleteUrl = "https://api.cirrus.center/v2/data/database/delete?key=$key";
// $deleteResponse = file_get_contents($deleteUrl);

echo json_encode(['success' => true]);
?>
