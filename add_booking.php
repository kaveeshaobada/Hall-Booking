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
$hallId = $_GET["hallId"] ?? "";
$error = "";

// Fetch hall details to get capacity
$ch = curl_init("http://203.94.72.18/trainee/api/production/hall/get/one/$hallId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
handleApiError($httpCode, $response);
checkToken($httpCode);
checkToken($response);
$hall = json_decode($response, true);
$capacity = $hall["capacity"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $participants = (int)$_POST["expectedParticipants"];
    $startTime = $_POST["startTime"];
    $endTime = $_POST["endTime"];
    $reservedDate = $_POST["reservedDate"];

    // Validation
    if ($participants > $capacity) {
        $error = "Participants ($participants) exceed hall capacity ($capacity).";
    } elseif ($endTime <= $startTime) {
        $error = "End time must be after start time.";
    } elseif ($reservedDate < date("Y-m-d")) {
        $error = "Reserved date cannot be in the past.";
    } else {
        // Check duplicate bookings
        $allBookings = json_decode(file_get_contents_with_auth(
            "http://203.94.72.18/trainee/api/production/booking/get/all/bookings",
            $token
        ), true);

        $duplicate = false;
        foreach ($allBookings as $b) {
            if ($b["hall"]["id"] === $hallId && $b["reservedDate"] === $reservedDate) {
                $existingStart = $b["startTime"];
                $existingEnd = $b["endTime"];
                if ($startTime < $existingEnd && $endTime > $existingStart) {
                    $duplicate = true;
                    break;
                }
            }
        }

        if ($duplicate) {
            $error = "This hall is already booked during that time slot.";
        } else {
            $now = date("Y-m-d\TH:i:s");
            $data = json_encode([
                "reservedDate"         => $reservedDate,
                "startTime"            => $startTime,
                "endTime"              => $endTime,
                "bookingFor"           => $_POST["bookingFor"],
                "expectedParticipants" => $participants,
                "specialRequirements"  => $_POST["specialRequirements"],
                "hall"                 => ["id" => $hallId],
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
    }
}

function file_get_contents_with_auth($url, $token) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    return curl_exec($ch);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Booking</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Book Hall: <?php echo $hall["name"]; ?></h2>
<p>Location: <?php echo $hall["location"]; ?> | Capacity: <strong><?php echo $capacity; ?></strong></p>
<a href="index.php">Back</a><br><br>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

<div class="container">
    <div class="card">
        <h2>Add New Booking</h2>

        <form method="POST" action="add_booking.php?hallId=<?php echo $hallId; ?>">
            Booking For: <input type="text" name="bookingFor" required><br><br>
            Reserved Date: <input type="date" name="reservedDate" min="<?php echo date('Y-m-d'); ?>" required><br><br>
            Start Time: <input type="time" name="startTime" required><br><br>
            End Time: <input type="time" name="endTime" required><br><br>
            Expected Participants: 
            <input type="number" name="expectedParticipants" min="1" max="<?php echo $capacity; ?>" required><br><br>
            <small>Max capacity: <?php echo $capacity; ?></small><br><br>
            Special Requirements: <textarea name="specialRequirements"></textarea><br><br>
            <button type="submit">Book Hall</button>
        </form>
    </div>
</div>

</body>
</html>