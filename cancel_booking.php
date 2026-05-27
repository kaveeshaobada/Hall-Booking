<?php

include 'error_handler.php';

session_start();

function checkToken($httpCode) {
    if ($httpCode == 401) {
        session_destroy();
        header("Location: login.php?expired=1");
        exit();
    }
}

if (!isset($_SESSION["token"])) {
    header("Location: login.php");
    exit();
}

$token = $_SESSION["token"];
$id = $_GET["id"];
$now = date("Y-m-d\TH:i:s");

$data = json_encode([
    "id"        => $id,
    "status"    => false,
    "updatedAt" => $now
]);

$ch = curl_init("http://203.94.72.18/trainee/api/production/booking/update/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $token"
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
handleApiError($httpCode, $response);
checkToken($httpCode);

header("Location: bookings.php");
exit();
?>