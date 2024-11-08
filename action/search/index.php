<?php
require_once '../../config.php';

if (!isset($_COOKIE['auth'])) {
    header('Location: /login.php');
    exit();
}

$token = $_COOKIE['auth'];
$apikey = $cirrusapi;

// Get user ID
$userInfo = file_get_contents("https://api.cirrus.center/v2/auth/user/", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $apikey\r\nToken: $token\r\n"
    ]
]));

$userInfo = json_decode($userInfo, true);
$userId = $userInfo['user']['id'];

$containerId = $_GET['container'] ?? null;
$item = $_GET['item'] ?? null;
$all = $_GET['all'] ?? null;

$response = [
    'total_entries' => 0,
    'data' => []
];

if ($all) {
    // Fetch all data from the user database
    $data = file_get_contents("https://api.cirrus.center/v2/data/database/?db=AIMS&log={$userId}", false, stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer $apikey\r\n"
        ]
    ]));

    $data = json_decode($data, true);

    foreach ($data['data'] as $containerId => $items) {
        foreach ($items as $entryId => $itemEntry) {
            $parts = explode('|', $itemEntry);
            $response['data'][] = [
                'containerId' => $containerId,
                'entryId' => $entryId,
                'url' => str_replace('\/', '/', $parts[2]),
                'caption' => "$parts[0] - " . $parts[3]
            ];
            $response['total_entries']++;
        }
    }
} elseif ($containerId) {
    // Fetch data from the database for container
    $data = file_get_contents("https://api.cirrus.center/v2/data/database/?db=AIMS&log={$userId}{$containerId}", false, stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer $apikey\r\n"
        ]
    ]));

    $data = json_decode($data, true);
    $response['total_entries'] = $data['total_entries'];

    foreach ($data['data'] as $containerData) {
        foreach ($containerData as $entryId => $item) {
            $parts = explode('|', $item);
            $response['data'][] = [
                'containerId' => $containerId,
                'entryId' => $entryId,
                'url' => str_replace('\/', '/', $parts[0]),
                'caption' => $parts[1]
            ];
        }
    }
} elseif ($item) {
    // Fetch data from the database for item
    $data = file_get_contents("https://api.cirrus.center/v2/data/database/?db=AIMS&log=$userId", false, stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer $apikey\r\n"
        ]
    ]));

    $data = json_decode($data, true);

    foreach ($data['data'] as $containerId => $items) {
        foreach ($items as $entryId => $itemEntry) {
            $parts = explode('|', $itemEntry);
            if (stripos($parts[3], $item) !== false) {
                $response['data'][] = [
                    'containerId' => $containerId,
                    'entryId' => $entryId,
                    'url' => str_replace('\/', '/', $parts[2]),
                    'caption' => "$parts[0] - " . $parts[3]
                ];
                $response['total_entries']++;
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_SLASHES);
?>