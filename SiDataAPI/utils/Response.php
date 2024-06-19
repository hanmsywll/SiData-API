<?php
class Response {
    public static function send($data, $status_code = 200) {
        http_response_code($status_code);
        echo json_encode($data);
        exit;
    }
}
