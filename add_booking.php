<?php
session_start();
if (!isset($_SESSION["token"])) {
    header("Location: login.php");
    exit();
}

$token = $_SESSION["token"];
$userId = $_SESSION["userId"];
$hallId = $_GET["hallId"] ?? "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $now = date("Y-m-d\TH:i:s");

    $data = json_encode([
        "reservedDate"         => $_POST["reservedDate"],
        "startTime"            => $_POST["startTime"],
        "endTime"              => $_POST["endTime"],
        "bookingFor"           => $_POST["bookingFor"],
        "expectedParticipants" => (int)$_POST["expectedParticipants"],
        "specialRequirements"  => $_POST["specialRequirements"],
        "hall"                 => ["id" => $_POST["hallId"]],
        "requestedBy"          => ["userId" => $userId],
        "createdAt"            => $now
    ]);

    $ch = curl_init("http://203.94.72.18/trainee/api/production/booking/save");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $token"
    ]);
    $response = curl_exec($ch);
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
<head><title>Add Booking</title></head>
<body>

<h2>New Booking</h2>
<a href="index.php">Back</a><br><br>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

<form method="POST" action="add_booking.php">
    <input type="hidden" name="hallId" value="<?php echo $hallId; ?>">
    
    Booking For: <input type="text" name="bookingFor" required><br><br>
    Reserved Date: <input type="date" name="reservedDate" required><br><br>
    Start Time: <input type="time" name="startTime" required><br><br>
    End Time: <input type="time" name="endTime" required><br><br>
    Expected Participants: <input type="number" name="expectedParticipants" required><br><br>
    Special Requirements: <textarea name="specialRequirements"></textarea><br><br>
    <button type="submit">Book Hall</button>
</form>

</body>
</html>