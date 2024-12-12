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
curl_setopt($ch, CURLOPT_URL, "https://michael.sparrow.us-east.cirrusapi.com/v2/auth/user/");
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
$userInfo = file_get_contents("https://michael.sparrow.us-east.cirrusapi.com/v2/auth/user/", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $apikey\r\nToken: $token\r\n"
    ]
]));

$userInfo = json_decode($userInfo, true);
$userId = $userInfo['user']['id'];

$containerId = $_POST['containerId'] ?? null;
$caption = $_POST['caption'] ?? null;
$image = $_FILES['image'] ?? null;

if (!$containerId || !$caption) {
    echo json_encode(['success' => false, 'message' => 'Container ID and caption are required']);
    exit();
}

$imageUrl = 'https://cdn.cirrus.center/static/placeholder.png';

if ($image) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://michael.sparrow.us-east.cirrusapi.com/v2/data/upload/");
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

$entryData = "$imageUrl|$caption";

// Write to container database
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://michael.sparrow.us-east.cirrusapi.com/v2/data/database/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'db' => 'AIMS',
    'log' => "$userId$containerId",
    'entry' => 'NA',
    'value' => $entryData
]);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apikey"
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode != 200) {
    echo json_encode(['success' => false, 'message' => 'Error writing to container database']);
    exit();
}

$response = json_decode($response, true);
$entryId = $response['entry'];

// Write to user database
$userEntryData = "$containerId|$entryId|$imageUrl|$caption";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://michael.sparrow.us-east.cirrusapi.com/v2/data/database/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'db' => 'AIMS',
    'log' => $userId,
    'entry' => 'NA',
    'value' => $userEntryData
]);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apikey"
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode != 200) {
    echo json_encode(['success' => false, 'message' => 'Error writing to user database']);
    exit();
}

echo json_encode(['success' => true]);
?>
