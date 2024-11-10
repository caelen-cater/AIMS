<?php
include '../../config.php';

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_COOKIE['auth'])) {
    http_response_code(401);
    exit();
}

$apikey = $apikey;
$token = $_COOKIE['auth'];

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

http_response_code($httpcode);
?>