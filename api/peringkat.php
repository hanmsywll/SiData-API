<?php
require_once '../config/Database.php';
require_once '../utils/Response.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch ($requestMethod) {
    case 'GET':
        $query = "SELECT id_pengguna, nama, poin,pp FROM pengguna ORDER BY poin DESC limit 10";
        $stmt = $db->prepare($query);
        $stmt->execute();

        $peringkat = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::send($peringkat, 200);
        break;

    default:
        Response::send(["message" => "Method not allowed"], 405);
        break;
}
?>
