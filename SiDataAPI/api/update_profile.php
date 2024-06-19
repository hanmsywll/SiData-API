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
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::send(["message" => "Invalid email format"], 400);
            exit();
        }

        // Validate email domain
        $allowedDomains = ['gmail.com', 'yahoo.com'];
        $emailDomain = substr(strrchr($email, "@"), 1);
        if (!in_array($emailDomain, $allowedDomains)) {
            Response::send(["message" => "Email domain is not allowed"], 400);
            exit();
        }

        // Validate username (only letters)
        if (!preg_match('/^[a-zA-Z]+$/', $username)) {
            Response::send(["message" => "Username must contain only letters"], 400);
            exit();
        }

        // Check if the email is already used by another user
        $checkEmailQuery = "SELECT COUNT(*) as count FROM pengguna WHERE email = :email AND id_pengguna != :id_pengguna";
        $checkEmailStmt = $db->prepare($checkEmailQuery);
        $checkEmailStmt->bindParam(':email', $email);
        $checkEmailStmt->bindParam(':id_pengguna', $userId);
        $checkEmailStmt->execute();
        $emailResult = $checkEmailStmt->fetch(PDO::FETCH_ASSOC);

        // Check if the username is already used by another user
        $checkUsernameQuery = "SELECT COUNT(*) as count FROM pengguna WHERE nama = :username AND id_pengguna != :id_pengguna";
        $checkUsernameStmt = $db->prepare($checkUsernameQuery);
        $checkUsernameStmt->bindParam(':username', $username);
        $checkUsernameStmt->bindParam(':id_pengguna', $userId);
        $checkUsernameStmt->execute();
        $usernameResult = $checkUsernameStmt->fetch(PDO::FETCH_ASSOC);

        if ($emailResult['count'] > 0) {
            Response::send(["message" => "Email already in use"], 409);
        } elseif ($usernameResult['count'] > 0) {
            Response::send(["message" => "Username already in use"], 409);
        } else {
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
        }
    } else {
        Response::send(["message" => "All fields are required"], 400);
    }
} else {
    Response::send(["message" => "Method not allowed"], 405);
}
?>
