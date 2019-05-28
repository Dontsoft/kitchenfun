<?php

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (!defined('API_KEY') || strlen(API_KEY) <= 0) {
    echo json_encode((object)["message" => "API key missing"]);
    http_response_code(500);
    exit(500);
}

if (!isset($_POST["addresse"])) {
    echo json_encode((object)["message" => "Bad request"]);
    http_response_code(400);
    exit(400);
}

$addresse = $_POST['addresse'];
if (!is_string($addresse) || strlen($addresse) <= 0) {
    echo json_encode((object)["message" => "Invalid data"]);
    http_response_code(400);
    exit(400);
}

use GuzzleHttp\Client;

sleep(1);
$client = new Client(['base_uri' => 'https://api.opencagedata.com/geocode/v1/']);

$response = $client->request('GET', 'geojson', [
    'query' => [
        "q" => $addresse . ", Ilmenau",
        "key" => API_KEY,
        "pretty" => 1
    ]
]);
$content = json_decode($response->getBody()->getContents());
$features = $content->features;
$geometry = $features[0];

http_response_code(200);
echo json_encode($content->features[0]);
exit();
