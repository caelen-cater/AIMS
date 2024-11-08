<?php
include '../../config.php';

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

http_response_code($httpcode);
?>
