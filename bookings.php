<?php
session_start();
if (!isset($_SESSION["token"])) {
    header("Location: login.php");
    exit();
}

$token = $_SESSION["token"];

$ch = curl_init("http://203.94.72.18/trainee/api/production/booking/get/all/bookings");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$response = curl_exec($ch);

$bookings = json_decode($response, true);
?>

<!DOCTYPE html>
<html>
<head><title>All Bookings</title></head>
<body>

<h2>All Bookings</h2>
<a href="index.php">Back to Halls</a><br><br>

<table border="1" cellpadding="8">
    <tr>
        <th>Booking For</th>
        <th>Hall</th>
        <th>Location</th>
        <th>Date</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Participants</th>
        <th>Special Requirements</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($bookings as $booking): ?>
    <tr>
        <td><?php echo $booking["bookingFor"]; ?></td>
        <td><?php echo $booking["hall"]["name"]; ?></td>
        <td><?php echo $booking["hall"]["location"]; ?></td>
        <td><?php echo $booking["reservedDate"]; ?></td>
        <td><?php echo $booking["startTime"]; ?></td>
        <td><?php echo $booking["endTime"]; ?></td>
        <td><?php echo $booking["expectedParticipants"]; ?></td>
        <td><?php echo $booking["specialRequirements"]; ?></td>
        <td><?php echo $booking["status"] ? "Confirmed" : "Pending"; ?></td>
        <td>
            <a href="update_booking.php?id=<?php echo $booking["id"]; ?>">Edit</a> |
            <a href="cancel_booking.php?id=<?php echo $booking["id"]; ?>" 
                onclick="return confirm('Cancel this booking?')">Cancel</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>