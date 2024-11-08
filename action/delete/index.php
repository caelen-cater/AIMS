<?php
require_once '../../config.php';

if (!isset($_COOKIE['auth'])) {
    header('Location: /login.php');
    exit();
}

$token = $_COOKIE['auth'];
$apikey = $cirrusapi;
$userId = null;

// Get user ID
$userInfo = file_get_contents("https://api.cirrus.center/v2/auth/user/", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $apikey\r\nToken: $token\r\n"
    ]
]));

$userInfo = json_decode($userInfo, true);
$userId = $userInfo['user']['id'];

$containerId = $_GET['container'] ?? null;
$entryId = $_GET['entry'] ?? null;

if ($containerId && $entryId) {
    // Fetch user database to find the correct container ID and entry ID
    $userDbUrl = "https://api.cirrus.center/v2/data/database/?db=AIMS&log={$userId}";

    $userDbData = file_get_contents($userDbUrl, false, stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer $apikey\r\n"
        ]
    ]));

    $userDbData = json_decode($userDbData, true);

    foreach ($userDbData['data'] as $container => $items) {
        foreach ($items as $entry => $itemEntry) {
            $parts = explode('|', $itemEntry);
            if ($parts[0] == $containerId && $parts[1] == $entryId) {
                // Delete from container database
                $url = "https://api.cirrus.center/v2/data/database/?db=AIMS&log={$userId}{$parts[0]}&entry={$parts[1]}";

                $options = [
                    'http' => [
                        'method' => 'DELETE',
                        'header' => "Authorization: Bearer $apikey\r\n"
                    ]
                ];

                $context = stream_context_create($options);
                $result = file_get_contents($url, false, $context);

                if ($result === FALSE) {
                    echo json_encode(['success' => false, 'message' => 'Error deleting item from container database']);
                    exit();
                }

                // Delete from user database
                $deleteUrl = "https://api.cirrus.center/v2/data/database/?db=AIMS&log={$userId}&entry={$entry}";

                $deleteOptions = [
                    'http' => [
                        'method' => 'DELETE',
                        'header' => "Authorization: Bearer $apikey\r\n"
                    ]
                ];

                $deleteContext = stream_context_create($deleteOptions);
                $deleteResult = file_get_contents($deleteUrl, false, $deleteContext);

                if ($deleteResult === FALSE) {
                    echo json_encode(['success' => false, 'message' => 'Error deleting item from user database']);
                    exit();
                }

                echo json_encode(['success' => true]);
                exit();
            }
        }
    }

    echo json_encode(['success' => false, 'message' => 'Item not found in user database']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
}
?>
