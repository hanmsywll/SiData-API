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
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $query = "SELECT survei.id_survei, judul_survei, deskripsi_survei, pengguna.nama AS nama_pengguna, pertanyaan.id_pertanyaan, pertanyaan, jenis_pertanyaan, pilihan 
                      FROM pertanyaan 
                      JOIN survei ON pertanyaan.id_survei = survei.id_survei
                      JOIN pengguna ON survei.id_pengguna = pengguna.id_pengguna
                      WHERE pertanyaan.id_survei = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            $survey = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::send($survey);
        } elseif (isset($_GET['survey_id'])) {
            $survey_id = $_GET['survey_id'];
            $query = "SELECT pertanyaan.id_pertanyaan, jenis_pertanyaan, pertanyaan, pilihan, survei.judul_survei, survei.deskripsi_survei, pengguna.nama AS nama_pengguna 
                      FROM pertanyaan 
                      JOIN survei ON pertanyaan.id_survei = survei.id_survei
                      JOIN pengguna ON survei.id_pengguna = pengguna.id_pengguna
                      WHERE pertanyaan.id_survei = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $survey_id);
            $stmt->execute();
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::send($questions);
        } else {
            $query = "SELECT survei.id_survei, survei.judul_survei, survei.deskripsi_survei, pengguna.nama AS nama_pengguna 
                      FROM survei
                      JOIN pengguna ON survei.id_pengguna = pengguna.id_pengguna";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::send($surveys);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['answers'])) {
            $query = "INSERT INTO jawaban (id_pertanyaan, isi_jawaban) VALUES (:id_pertanyaan, :isi_jawaban)";
            $stmt = $db->prepare($query);
            foreach ($data['answers'] as $answer) {
                $stmt->bindParam(":id_pertanyaan", $answer['id_pertanyaan']);
                $stmt->bindParam(":isi_jawaban", $answer['isi_jawaban']);
                $stmt->execute();
            }
            Response::send(["message" => "Jawaban disimpan dengan sukses!"], 201);
        } else {
            $query = "INSERT INTO survei (judul_survei, deskripsi_survei, kategori_survei, kriteria_responden, id_pengguna) 
                      VALUES (:judul_survei, :deskripsi_survei, :kategori_survei, :kriteria_responden, :id_pengguna)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":judul_survei", $data['judul_survei']);
            $stmt->bindParam(":deskripsi_survei", $data['deskripsi_survei']);
            $stmt->bindParam(":kategori_survei", $data['kategori_survei']);
            $stmt->bindParam(":kriteria_responden", $data['kriteria_responden']);
            $stmt->bindParam(":id_pengguna", $data['id_pengguna']);

            if ($stmt->execute()) {
                $lastId = $db->lastInsertId();
                foreach ($data['questions'] as $question) {
                    $query = "INSERT INTO pertanyaan (jenis_pertanyaan, pertanyaan, pilihan, id_survei) 
                              VALUES (:jenis_pertanyaan, :pertanyaan, :pilihan, :id_survei)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(":jenis_pertanyaan", $question['questionType']);
                    $stmt->bindParam(":pertanyaan", $question['question']);
                    $stmt->bindParam(":pilihan", $question['options']);
                    $stmt->bindParam(":id_survei", $lastId);
                    $stmt->execute();
                }
                Response::send(["id_survei" => $lastId, "message" => "Survey created successfully!"], 201);
            } else {
                Response::send(["message" => "Failed to create survey"], 400);
            }
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id_survei'];
        $query = "UPDATE survei SET judul_survei = :judul_survei, deskripsi_survei = :deskripsi_survei, 
                  kategori_survei = :kategori_survei, kriteria_responden = :kriteria_responden 
                  WHERE id_survei = :id_survei";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":judul_survei", $data['judul_survei']);
        $stmt->bindParam(":deskripsi_survei", $data['deskripsi_survei']);
        $stmt->bindParam(":kategori_survei", $data['kategori_survei']);
        $stmt->bindParam(":kriteria_responden", $data['kriteria_responden']);
        $stmt->bindParam(":id_survei", $id);

        if ($stmt->execute()) {
            Response::send(["message" => "Survey updated successfully!"], 200);
        } else {
            Response::send(["message" => "Failed to update survey"], 400);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $query = "DELETE FROM survei WHERE id_survei = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            Response::send(["message" => "Survey deleted successfully!"], 200);
        } else {
            Response::send(["message" => "Failed to delete survey"], 400);
        }
        break;

    default:
        Response::send(["message" => "Method not allowed"], 405);
        break;
}
?>
