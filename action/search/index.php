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

// Get user ID
$userInfo = file_get_contents("https://michael.sparrow.us-east.cirrusapi.com/v2/auth/user/", false, stream_context_create([
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

function matchesSearchQuery($itemDescription, $searchQuery) {
    $searchWords = explode(' ', strtolower($searchQuery));
    foreach ($searchWords as $word) {
        if (stripos($itemDescription, $word) === false) {
            return false;
        }
    }
    return true;
}

// Enhanced function to handle flexible and intelligent matching
function matchesSearchQueryEnhanced($itemDescription, $searchQuery) {
    $searchWords = explode(' ', strtolower($searchQuery));
    $itemWords = explode(' ', strtolower($itemDescription));
    
    foreach ($searchWords as $searchWord) {
        $matchFound = false;
        foreach ($itemWords as $itemWord) {
            if (stripos($itemWord, $searchWord) !== false) {
                $matchFound = true;
                break;
            }
        }
        if (!$matchFound) {
            return false;
        }
    }
    return true;
}

// Decode URL-encoded characters before matching
function decodeAndMatch($itemDescription, $searchQuery) {
    $decodedDescription = urldecode($itemDescription);
    $decodedSearchQuery = urldecode($searchQuery);
    return matchesSearchQueryEnhanced($decodedDescription, $decodedSearchQuery);
}

if ($all) {
    // Fetch all data from the user database
    $data = file_get_contents("https://michael.sparrow.us-east.cirrusapi.com/v2/data/database/?db=AIMS&log={$userId}", false, stream_context_create([
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
    $data = file_get_contents("https://michael.sparrow.us-east.cirrusapi.com/v2/data/database/?db=AIMS&log={$userId}{$containerId}", false, stream_context_create([
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
    $data = file_get_contents("https://michael.sparrow.us-east.cirrusapi.com/v2/data/database/?db=AIMS&log=$userId", false, stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer $apikey\r\n"
        ]
    ]));

    $data = json_decode($data, true);

    if (strtolower($item) === 'index') {
        // Show all entries if the search query is "index"
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
    } else {
        foreach ($data['data'] as $containerId => $items) {
            foreach ($items as $entryId => $itemEntry) {
                $parts = explode('|', $itemEntry);
                $itemDescription = strtolower($parts[3]);
                if (decodeAndMatch($itemDescription, $item)) {
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
}

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_SLASHES);
?>
