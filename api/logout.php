<?php
session_start();

$response = array();

if (session_id()) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    $response['status'] = 'success';
    $response['message'] = 'Successfully logged out';
} else {
    $response['status'] = 'error';
    $response['message'] = 'No active session found';
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
