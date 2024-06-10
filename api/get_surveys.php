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
        if (isset($_GET['id_survei'])) {
            // Handle the case where a specific survey is requested
            $surveyId = $_GET['id_survei'];

            $query = "SELECT * FROM survei WHERE id_survei = :survey_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':survey_id', $surveyId);
            $stmt->execute();

            $survey = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($survey) {
                Response::send($survey, 200);
            } else {
                Response::send(["message" => "Survey not found"], 404);
            }
            exit();
        }

        if (!isset($_GET['user_id'])) {
            Response::send(["message" => "User ID is required"], 400);
            exit();
        }

        $userId = $_GET['user_id'];

        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'responden':
                    if (!isset($_GET['survey_id'])) {
                        Response::send(["message" => "Survey ID is required"], 400);
                        exit();
                    }

                    $surveyId = $_GET['survey_id'];

                    $query = "SELECT DISTINCT p.id_pengguna, p.nama AS nama
                              FROM pengguna p
                              JOIN detail_jawaban dj ON p.id_pengguna = dj.id_pengguna
                              JOIN jawaban j ON dj.id_jawaban = j.id_jawaban
                              JOIN pertanyaan pt ON j.id_pertanyaan = pt.id_pertanyaan
                              WHERE pt.id_survei = :survey_id";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':survey_id', $surveyId);
                    $stmt->execute();

                    $respondents = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    Response::send($respondents, 200);
                    break;

                case 'answers':
                    if (!isset($_GET['survey_id'])) {
                        Response::send(["message" => "Survey ID is required"], 400);
                        exit();
                    }

                    $surveyId = $_GET['survey_id'];

                    $query = "SELECT pt.pertanyaan, j.isi_jawaban, p.nama AS respondent_name
                              FROM jawaban j
                              JOIN pertanyaan pt ON j.id_pertanyaan = pt.id_pertanyaan
                              JOIN detail_jawaban dj ON j.id_jawaban = dj.id_jawaban
                              JOIN pengguna p ON dj.id_pengguna = p.id_pengguna
                              WHERE pt.id_survei = :survey_id";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':survey_id', $surveyId);
                    $stmt->execute();

                    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    Response::send($answers, 200);
                    break;

                case 'individual_answers':
                    if (!isset($_GET['survey_id']) || !isset($_GET['respondent_id'])) {
                        Response::send(["message" => "Survey ID and Respondent ID are required"], 400);
                        exit();
                    }

                    $surveyId = $_GET['survey_id'];
                    $respondentId = $_GET['respondent_id'];

                    $query = "SELECT pt.pertanyaan, j.isi_jawaban
                              FROM jawaban j
                              JOIN pertanyaan pt ON j.id_pertanyaan = pt.id_pertanyaan
                              JOIN detail_jawaban dj ON j.id_jawaban = dj.id_jawaban
                              WHERE pt.id_survei = :survey_id AND dj.id_pengguna = :respondent_id";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':survey_id', $surveyId);
                    $stmt->bindParam(':respondent_id', $respondentId);
                    $stmt->execute();

                    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    Response::send($answers, 200);
                    break;

                default:
                    Response::send(["message" => "Invalid action"], 400);
                    break;
            }
        } else {
            $query = "SELECT * FROM survei WHERE id_pengguna = :user_id ORDER BY id_survei DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);

            Response::send($surveys, 200);
        }
        break;

    default:
        Response::send(["message" => "Method not allowed"], 405);
        break;
}
?>
