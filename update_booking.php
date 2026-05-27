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
$userId = $_SESSION["userId"];
$bookingId = $_GET["id"] ?? "";
$error = "";

// Fetch existing booking
$ch = curl_init("http://203.94.72.18/trainee/api/production/booking/get/one/$bookingId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
handleApiError($httpCode, $response);
checkToken($httpCode);
$booking = json_decode($response, true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $now = date("Y-m-d\TH:i:s");

    $data = json_encode([
        "id"                   => $bookingId,
        "reservedDate"         => $_POST["reservedDate"],
        "startTime"            => $_POST["startTime"],
        "endTime"              => $_POST["endTime"],
        "bookingFor"           => $_POST["bookingFor"],
        "expectedParticipants" => (int)$_POST["expectedParticipants"],
        "specialRequirements"  => $_POST["specialRequirements"],
        "hall"                 => ["id" => $booking["hall"]["id"]],
        "requestedBy"          => ["userId" => $userId],
        "createdAt"            => $now
    ]);

    $ch = curl_init("http://203.94.72.18/trainee/api/production/booking/update");
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
    $result = json_decode($response, true);

    if (isset($result["message"]) && $result["message"] == "Saved successfully!") {
        header("Location: bookings.php");
        exit();
    } else {
        $error = "Failed: " . $response;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Booking</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Update Booking</h2>
<a href="bookings.php">Back</a><br><br>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>


<div class="container">
    <div class="card">
        <h2>Update Booking</h2>
        <form method="POST" action="update_booking.php?id=<?php echo $bookingId; ?>">
            Booking For: <input type="text" name="bookingFor" value="<?php echo $booking["bookingFor"]; ?>" required><br><br>
            Reserved Date: <input type="date" name="reservedDate" value="<?php echo $booking["reservedDate"]; ?>" required><br><br>
            Start Time: <input type="time" name="startTime" value="<?php echo substr($booking["startTime"], 0, 5); ?>" required><br><br>
            End Time: <input type="time" name="endTime" value="<?php echo substr($booking["endTime"], 0, 5); ?>" required><br><br>
            Expected Participants: <input type="number" name="expectedParticipants" value="<?php echo $booking["expectedParticipants"]; ?>" required><br><br>
            Special Requirements: <textarea name="specialRequirements"><?php echo $booking["specialRequirements"]; ?></textarea><br><br>
            <button type="submit">Update Booking</button>
        </form>
    </div>
</div>

</body>
</html>