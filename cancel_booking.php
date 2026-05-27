<?php
session_start();
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

header("Location: bookings.php");
exit();
?>