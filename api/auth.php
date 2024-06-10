<?php
require_once '../config/Database.php';
require_once '../utils/Response.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch ($requestMethod) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $action = $data['action'];

        if ($action == 'register') {
            $nama = $data['username'];
            $pasword = password_hash($data['password'], PASSWORD_BCRYPT);
            $email = isset($data['email']) ? $data['email'] : '';

            if (empty($email)) {
                Response::send(["message" => "Email tidak boleh kosong"], 400);
                exit();
            }
            
            $checkUserQuery = "SELECT * FROM pengguna WHERE email = :email";
            $stmt = $db->prepare($checkUserQuery);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                Response::send(["message" => "Email sudah digunakan"], 400);
            } else {
                $query = "INSERT INTO pengguna (nama, pasword, email) VALUES (:nama, :pasword, :email)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':pasword', $pasword);
                $stmt->bindParam(':email', $email);

                if ($stmt->execute()) {
                    Response::send(["message" => "Registrasi berhasil"], 201);
                } else {
                    Response::send(["message" => "Registrasi gagal"], 500);
                }
            }
        } elseif ($action == 'login') {
            $nama = $data['username'];
            $pasword = $data['password'];

            $query = "SELECT * FROM pengguna WHERE nama = :nama";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama', $nama);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($pasword, $user['pasword'])) {
                    Response::send([
                        "message" => "Login berhasil",
                        "user_id" => $user['id_pengguna'],
                        "username" => $user['nama']
                    ], 200);
                } else {
                    Response::send(["message" => "Password salah"], 401);
                }
            } else {
                Response::send(["message" => "Username tidak ditemukan"], 404);
            }
        }
        break;

    default:
        Response::send(["message" => "Method not allowed"], 405);
        break;
}
?>
