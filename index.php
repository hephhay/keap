<?php

$url = 'https://api.infusionsoft.com/crm/rest/v2/contacts/';

define('CUSTOM_ID', 'custom_id');
define('API_KEY', 'HTTP_API_KEY');
define('JSON_CONTENT_TYPE', 'application/json');

define("GET", "GET");
define("POST", "POST");
define("PUT", "PUT");
define("PATCH", "PATCH");
define("DELETE", "DELETE");
define("OPTIONS", "OPTIONS");

define("HTTP_OK", 200);
define("HTTP_METHOD_NOT_ALLOWED", 405);
define("HTTP_BAD_REQUEST", 400);
define('HTTP_UNPROCESSABLE_ENTITY', 422);
define('HTTP_INTERNAL_SERVER_ERROR', 500);

$method = $_SERVER['REQUEST_METHOD'];

// get data from request
$data = json_decode(file_get_contents('php://input'), true);
//throw error if data is not json
if ((json_last_error() !== JSON_ERROR_NONE) && ($method === POST || $method === PATCH)) {
    http_response_code(HTTP_UNPROCESSABLE_ENTITY);
    echo json_encode(array('message' => 'Invalid data'));
    exit;
}

// get key from header or respond with error
$key = $_SERVER[API_KEY];
if (!$key) {
    http_response_code(HTTP_BAD_REQUEST);
    echo json_encode(array('message' => 'API key is required'));
    exit;
}

// handle preflight request
if ($method === OPTIONS) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, ' . API_KEY);
    header('Access-Control-Max-Age: 86400');
} else if ($method === GET || $method === POST || $method === PATCH || $method === DELETE) {
    $param = $_GET;
    // check if CUSTOM_ID is in param and append to url
    if (($method === PATCH || $method === DELETE) && array_key_exists(CUSTOM_ID, $param)) {
        $url = $url . $param[CUSTOM_ID];
        unset($param[CUSTOM_ID]);
    }
    // make request
    $curl = curl_init();

    if ($method === GET) {
        $url = $url . '?' . http_build_query($param);
    }

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => array(
            'X-Keap-API-Key: '. $key,
            'Content-Type: ' . JSON_CONTENT_TYPE
        )
    ));

    if ($method === POST) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    } else if ($method === PATCH) {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, PATCH);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    } else if ($method === DELETE) {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, DELETE);
    }

    $response = curl_exec($curl);
    curl_close($curl);

    //respond with error if request failed
    if (curl_errno($curl)) {
        http_response_code(HTTP_INTERNAL_SERVER_ERROR);
        echo json_encode(array('message' => 'Request failed'));
        return;
    }

    //set status code from response
    http_response_code(curl_getinfo($curl, CURLINFO_HTTP_CODE));
    header('Content-Type: application/json');
    echo $response;
} else {
    http_response_code(HTTP_METHOD_NOT_ALLOWED);
    echo json_encode(array('message' => 'Method not allowed'));
}
