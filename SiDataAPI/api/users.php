<?php
require_once '../config/Database.php';
require_once '../utils/Response.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == 'GET') {
    $stmt = $db->query('SELECT * FROM pengguna');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    Response::send($users);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
}
