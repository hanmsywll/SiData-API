<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == 'GET') {
    echo json_encode(["message" => "Welcome to SiData API"]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
}
