<?php
include '../../../config.php'; // Assuming this file contains $cirrusapi

$token = $_COOKIE['token']; // Assuming the token is stored in a cookie

$authUrl = "https://api.cirrus.center/v2/auth/user/";
$options = [
    'http' => [
        'method' => 'GET',
        'header' => [
            "Authorization: Bearer $cirrusapi",
            "Token: $token"
        ]
    ]
];

$context = stream_context_create($options);
$authResponse = file_get_contents($authUrl, false, $context);
$authData = json_decode($authResponse, true);

if ($http_response_header[0] == 'HTTP/1.1 401 Unauthorized') {
    header('Location: ../../../login');
    exit;
}

$userid = $authData['user']['id'];
$containerId = $_GET['containerId'];
$url = "https://api.cirrus.center/v2/data/database/?db=AIMS&log=$userid.$containerId";

$response = file_get_contents($url);
echo $response;
?>