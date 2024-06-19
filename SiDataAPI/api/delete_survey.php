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
if ($requestMethod === 'DELETE') {
    // Mengambil parameter dari URL
    if (isset($_GET['id_survei'])) {
        $survey_id = $_GET['id_survei'];

        // Mulai transaksi untuk memastikan integritas data
        $db->beginTransaction();

        try {
            // Menghapus detail jawaban terkait
            $deleteDetailAnswersQuery = "
                DELETE detail_jawaban 
                FROM detail_jawaban 
                JOIN jawaban ON detail_jawaban.id_jawaban = jawaban.id_jawaban 
                JOIN pertanyaan ON jawaban.id_pertanyaan = pertanyaan.id_pertanyaan 
                WHERE pertanyaan.id_survei = :id_survei";
            $stmt = $db->prepare($deleteDetailAnswersQuery);
            $stmt->bindParam(':id_survei', $survey_id);
            $stmt->execute();

            // Menghapus jawaban terkait
            $deleteAnswersQuery = "
                DELETE jawaban 
                FROM jawaban 
                JOIN pertanyaan ON jawaban.id_pertanyaan = pertanyaan.id_pertanyaan 
                WHERE pertanyaan.id_survei = :id_survei";
            $stmt = $db->prepare($deleteAnswersQuery);
            $stmt->bindParam(':id_survei', $survey_id);
            $stmt->execute();

            // Menghapus pertanyaan terkait
            $deleteQuestionsQuery = "DELETE FROM pertanyaan WHERE id_survei = :id_survei";
            $stmt = $db->prepare($deleteQuestionsQuery);
            $stmt->bindParam(':id_survei', $survey_id);
            $stmt->execute();

            // Menghapus survei
            $deleteSurveyQuery = "DELETE FROM survei WHERE id_survei = :id_survei";
            $stmt = $db->prepare($deleteSurveyQuery);
            $stmt->bindParam(':id_survei', $survey_id);
            $stmt->execute();

            // Commit transaksi
            $db->commit();

            Response::send(["message" => "Survey and related data deleted successfully."], 200);
        } catch (Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            $db->rollBack();
            Response::send(["message" => "Failed to delete survey. Error: " . $e->getMessage()], 500);
        }
    } else {
        Response::send(["message" => "Survey ID not provided."], 400);
    }
} else {
    Response::send(["message" => "Method not allowed"], 405);
}
?>
