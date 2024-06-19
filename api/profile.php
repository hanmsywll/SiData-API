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
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        if ($userId) {
            $query = "SELECT id_pengguna, nama, poin, waktu_buatakun, email, pp FROM pengguna WHERE id_pengguna = :id_pengguna";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_pengguna', $userId);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                Response::send($user, 200);
            } else {
                Response::send(["message" => "User not found"], 404);
            }
        } else {
            Response::send(["message" => "User ID is required"], 400);
        }
        break;

    default:
        Response::send(["message" => "Method not allowed"], 405);
        break;
}
?>
