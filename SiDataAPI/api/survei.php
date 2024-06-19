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

        if ($action == 'create_survey') {
            $judul_survei = $data['judul_survei'];
            $deskripsi_survei = $data['deskripsi_survei'];
            $id_pengguna = $data['id_pengguna'];

            $query = "INSERT INTO survei (tanggal_buat, judul_survei, deskripsi_survei, id_pengguna) 
                      VALUES (CURRENT_DATE(), :judul_survei, :deskripsi_survei, :id_pengguna)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':judul_survei', $judul_survei);
            $stmt->bindParam(':deskripsi_survei', $deskripsi_survei);
            $stmt->bindParam(':id_pengguna', $id_pengguna);

            if ($stmt->execute()) {
                $id_survei = $db->lastInsertId();

                $questions = $data['questions'];
                foreach ($questions as $question) {
                    $questionText = $question['question'];
                    $questionType = $question['questionType'];
                    $options = isset($question['options']) ? implode(", ", $question['options']) : null;

                    $query = "INSERT INTO pertanyaan (jenis_pertanyaan, pertanyaan, pilihan, id_survei) 
                              VALUES (:jenis_pertanyaan, :pertanyaan, :pilihan, :id_survei)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':jenis_pertanyaan', $questionType);
                    $stmt->bindParam(':pertanyaan', $questionText);
                    $stmt->bindParam(':pilihan', $options);
                    $stmt->bindParam(':id_survei', $id_survei);

                    if (!$stmt->execute()) {
                        Response::send(["message" => "Gagal menambahkan pertanyaan"], 500);
                        exit();
                    }
                }

                Response::send(["message" => "Survei dan pertanyaan berhasil dibuat", "id_survei" => $id_survei], 201);
            } else {
                Response::send(["message" => "Gagal membuat survei"], 500);
            }
        } else if ($action == 'update_survey') {
            $id_survei = $data['id_survei'];
            $kriteria_responden = $data['kriteria_responden'];
            $target_jumlah = $data['target_jumlah'];
            $kategori_survei = $data['kategori_survei'];

            $query = "UPDATE survei SET kriteria_responden = :kriteria_responden, target_jumlah = :target_jumlah, kategori_survei = :kategori_survei WHERE id_survei = :id_survei";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':kriteria_responden', $kriteria_responden);
            $stmt->bindParam(':target_jumlah', $target_jumlah);
            $stmt->bindParam(':kategori_survei', $kategori_survei);
            $stmt->bindParam(':id_survei', $id_survei);

            if ($stmt->execute()) {
                Response::send(["message" => "Survey updated successfully"], 200);
            } else {
                Response::send(["message" => "Failed to update survey"], 500);
            }
        } else if ($action == 'update_survey_settings') {
            $id_survei = $data['id_survei'];
            $kriteria_responden = $data['kriteria_responden'];
            $target_jumlah = $data['target_jumlah'];
            $startDate = $data['startDate'];
            $endDate = $data['endDate'];
            $categories = $data['categories'];

            $query = "UPDATE survei SET 
                        kriteria_responden = :kriteria_responden, 
                        target_jumlah = :target_jumlah, 
                        tanggal_mulai = :tanggal_mulai, 
                        tanggal_selesai = :tanggal_selesai, 
                        kategori_survei = :kategori_survei 
                      WHERE id_survei = :id_survei";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':kriteria_responden', $kriteria_responden);
            $stmt->bindParam(':target_jumlah', $target_jumlah);
            $stmt->bindParam(':tanggal_mulai', $startDate);
            $stmt->bindParam(':tanggal_selesai', $endDate);
            $stmt->bindParam(':kategori_survei', $categories);
            $stmt->bindParam(':id_survei', $id_survei);

            if ($stmt->execute()) {
                Response::send(["message" => "Survey settings updated successfully"], 200);
            } else {
                Response::send(["message" => "Failed to update survey settings"], 500);
            }
        } else {
            Response::send(["message" => "Aksi tidak dikenali"], 400);
        }
        break;

    default:
        Response::send(["message" => "Method not allowed"], 405);
        break;
}
?>
