<?php
require_once '../config/Database.php';
require_once '../utils/Response.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $userId = $data['user_id'];
    $email = $data['email'];
    $username = $data['username'];

    if ($userId && $email && $username) {
        $query = "UPDATE pengguna SET email = :email, nama = :username WHERE id_pengguna = :id_pengguna";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id_pengguna', $userId);

        if ($stmt->execute()) {
            Response::send(["message" => "Profile updated successfully"], 200);
        } else {
            Response::send(["message" => "Failed to update profile"], 500);
        }
    } else {
        Response::send(["message" => "All fields are required"], 400);
    }
} else {
    Response::send(["message" => "Method not allowed"], 405);
}
?>
