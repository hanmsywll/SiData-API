<?php
require_once '../config/Database.php';
require_once '../utils/Response.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id']) && isset($_FILES['image'])) {
        $userId = $_POST['user_id'];
        $image = $_FILES['image'];
        
        $targetDir = "../uploads/";
        $fileName = basename($image["name"]);
        $targetFilePath = $targetDir . $fileName;
        
        if (move_uploaded_file($image["tmp_name"], $targetFilePath)) {
            $imageURL = "https://a2ae-125-164-21-172.ngrok-free.app/SiDataAPI/uploads/" . $fileName;
            
            $query = "UPDATE pengguna SET pp = :pp WHERE id_pengguna = :id_pengguna";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':pp', $imageURL);
            $stmt->bindParam(':id_pengguna', $userId);
            
            if ($stmt->execute()) {
                Response::send(["image_url" => $imageURL], 200);
            } else {
                Response::send(["message" => "Failed to update profile image"], 500);
            }
        } else {
            Response::send(["message" => "Failed to upload image"], 500);
        }
    } else {
        Response::send(["message" => "Required fields are missing"], 400);
    }
} else {
    Response::send(["message" => "Method not allowed"], 405);
}
?>
