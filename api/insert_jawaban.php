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

        if (isset($data['answers'])) {
            try {
                $db->beginTransaction();
                $query = "INSERT INTO jawaban (id_pertanyaan, isi_jawaban) VALUES (:id_pertanyaan, :isi_jawaban)";
                $stmt = $db->prepare($query);

                foreach ($data['answers'] as $answer) {
                    $id_pengguna = $answer['id_pengguna']; // Get id_pengguna from the answer array
                    $stmt->bindParam(":id_pertanyaan", $answer['id_pertanyaan']);
                    $stmt->bindParam(":isi_jawaban", $answer['isi_jawaban']);
                    $stmt->execute();

                    // Ambil ID jawaban yang baru saja disimpan
                    $id_jawaban = $db->lastInsertId();
                    $detQuery = "INSERT INTO detail_jawaban (id_pengguna, id_jawaban) VALUES (:id_pengguna, :id_jawaban)";
                    $detStmt = $db->prepare($detQuery);
                    $detStmt->bindParam(":id_pengguna", $id_pengguna);
                    $detStmt->bindParam(":id_jawaban", $id_jawaban);
                    $detStmt->execute();
                }

                // Tambah poin ke pengguna
                $tambahPoinQuery = "UPDATE pengguna SET poin = poin + 20 WHERE id_pengguna = :id_pengguna";
                $poinStmt = $db->prepare($tambahPoinQuery);
                $poinStmt->bindParam(":id_pengguna", $id_pengguna);
                $poinStmt->execute();

                $db->commit();
                Response::send(["message" => "Jawaban disimpan dengan sukses!"], 201);
            } catch (Exception $e) {
                $db->rollBack();
                Response::send(["message" => "Gagal menyimpan jawaban: " . $e->getMessage()], 400);
            }
        } else {
            Response::send(["message" => "Data jawaban tidak ditemukan"], 400);
        }
        break;

    default:
        Response::send(["message" => "Method not allowed"], 405);
        break;
}
?>
