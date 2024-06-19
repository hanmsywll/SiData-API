<?php
// Mengimpor file konfigurasi dan utilitas
require_once '../config/Database.php';
require_once '../utils/Response.php';

// Mengatur header
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Membuat koneksi database
$database = new Database();
$db = $database->getConnection();

// Mendapatkan metode request
$requestMethod = $_SERVER["REQUEST_METHOD"];

// Memeriksa metode HTTP
if ($requestMethod === 'POST') {
    // Mengambil data input dalam format JSON
    $data = json_decode(file_get_contents("php://input"), true);

    // Memeriksa apakah data input ada
    if (isset($data['id_survei'])) {
        $survey_id = $data['id_survei'];
        $updateFields = [];
        $params = [':id_survei' => $survey_id];

        // Memeriksa dan menambahkan setiap field yang tersedia ke query
        if (isset($data['judul_survei'])) {
            $updateFields[] = "judul_survei = :judul_survei";
            $params[':judul_survei'] = $data['judul_survei'];
        }
        if (isset($data['deskripsi_survei'])) {
            $updateFields[] = "deskripsi_survei = :deskripsi_survei";
            $params[':deskripsi_survei'] = $data['deskripsi_survei'];
        }
        if (isset($data['kategori_survei'])) {
            $updateFields[] = "kategori_survei = :kategori_survei";
            $params[':kategori_survei'] = $data['kategori_survei'];
        }
        if (isset($data['kriteria_responden'])) {
            $updateFields[] = "kriteria_responden = :kriteria_responden";
            $params[':kriteria_responden'] = $data['kriteria_responden'];
        }
        if (isset($data['target_jumlah'])) {
            $updateFields[] = "target_jumlah = :target_jumlah";
            $params[':target_jumlah'] = $data['target_jumlah'];
        }
        if (isset($data['tanggal_mulai'])) {
            $updateFields[] = "tanggal_mulai = :tanggal_mulai";
            $params[':tanggal_mulai'] = $data['tanggal_mulai'];
        }
        if (isset($data['tanggal_selesai'])) {
            $updateFields[] = "tanggal_selesai = :tanggal_selesai";
            $params[':tanggal_selesai'] = $data['tanggal_selesai'];
        }

        // Periksa apakah ada field yang akan diupdate
        if (count($updateFields) > 0) {
            // Membuat query untuk update data
            $updateSurveyQuery = "UPDATE survei SET " . implode(", ", $updateFields) . " WHERE id_survei = :id_survei";
            $stmt = $db->prepare($updateSurveyQuery);

            // Mengikat parameter ke query
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            // Menjalankan query dan memeriksa hasilnya
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    Response::send(["message" => "Survey updated successfully."], 200);
                } else {
                    Response::send(["message" => "No changes were made."], 200);
                }
            } else {
                Response::send(["message" => "Failed to update survey."], 500);
            }
        } else {
            Response::send(["message" => "No fields to update."], 400);
        }
    } else {
        Response::send(["message" => "Survey ID not provided."], 400);
    }
} else {
    Response::send(["message" => "Method not allowed"], 405);
}
?>
