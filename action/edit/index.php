<?php
require_once '../../config.php';

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_COOKIE['auth'])) {
    http_response_code(401);
    exit();
}

$token = $_COOKIE['auth'];
$apikey = $apikey;

// Validate token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.cirrus.center/v2/auth/user/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apikey",
    "Token: $token"
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode !== 200) {
    http_response_code(401);
    exit();
}

$userId = null;

// Get user ID
$userInfo = file_get_contents("https://api.cirrus.center/v2/auth/user/", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $apikey\r\nToken: $token\r\n"
    ]
]));

$userInfo = json_decode($userInfo, true);
$userId = $userInfo['user']['id'];

$containerId = $_POST['containerId'] ?? null;
$entryId = $_POST['entryId'] ?? null;
$caption = $_POST['caption'] ?? null;
$image = $_FILES['image'] ?? null;

if (!$containerId || !$entryId) {
    echo json_encode(['success' => false, 'message' => 'Container ID and entry ID are required']);
    exit();
}

// Fetch existing entry data
$data = file_get_contents("https://api.cirrus.center/v2/data/database/?db=AIMS&log={$userId}{$containerId}&entry={$entryId}", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $apikey\r\n"
    ]
]));

$data = json_decode($data, true);
$existingEntry = $data['data'];
$parts = explode('|', $existingEntry);

$imageUrl = $parts[0]; // Retain the current image URL
$currentCaption = $parts[1]; // Retain the current caption

if ($image) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.cirrus.center/v2/data/upload/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'file' => new CURLFile($image['tmp_name'], $image['type'], $image['name'])
    ]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apikey"
    ]);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 200) {
        $response = json_decode($response, true);
        $imageUrl = $response['url'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Error uploading image']);
        exit();
    }
}

if ($caption !== null) {
    $currentCaption = $caption;
}

$newEntryData = "$imageUrl|$currentCaption";

// Update container database
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.cirrus.center/v2/data/database/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'db' => 'AIMS',
    'log' => "$userId$containerId",
    'entry' => $entryId,
    'value' => $newEntryData
]);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apikey"
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode != 200) {
    echo json_encode(['success' => false, 'message' => 'Error updating container database']);
    exit();
}

// Update user database
$userEntryData = "$containerId|$entryId|$imageUrl|$currentCaption";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.cirrus.center/v2/data/database/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'db' => 'AIMS',
    'log' => $userId,
    'entry' => $entryId,
    'value' => $userEntryData
]);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apikey"
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode != 200) {
    echo json_encode(['success' => false, 'message' => 'Error updating user database']);
    exit();
}

echo json_encode(['success' => true]);
?>