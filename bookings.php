<?php

include 'error_handler.php';
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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
handleApiError($httpCode, $response);

$bookings = json_decode($response, true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Bookings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="navbar">
    <strong>Hall Booking System</strong>
    <div>
        <a href="index.php">Halls</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>All Bookings</h2>

    <table>
        <tr>
            <th>Booking For</th>
            <th>Hall</th>
            <th>Date</th>
            <th>Start</th>
            <th>End</th>
            <th>Participants</th>
            <th>Requirements</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($bookings as $booking): ?>
        <tr>
            <td><?php echo $booking["bookingFor"]; ?></td>
            <td><?php echo $booking["hall"]["name"]; ?></td>
            <td><?php echo $booking["reservedDate"]; ?></td>
            <td><?php echo $booking["startTime"]; ?></td>
            <td><?php echo $booking["endTime"]; ?></td>
            <td><?php echo $booking["expectedParticipants"]; ?></td>
            <td><?php echo $booking["specialRequirements"]; ?></td>
            <td>
                <?php
                $status = $booking["status"];
                if (is_null($status)) {
                    echo '<span class="badge-pending">Pending</span>';
                } elseif ($status == true) {
                    echo '<span class="badge-confirmed">Verified</span>';
                } else {
                    echo '<span class="badge-cancelled">Cancelled</span>';
                }
                ?>
            </td>
            <td>
                <a class="btn btn-warning" href="update_booking.php?id=<?php echo $booking["id"]; ?>">Edit</a>
                <a class="btn btn-danger" href="cancel_booking.php?id=<?php echo $booking["id"]; ?>"
                   onclick="return confirm('Cancel this booking?')">Cancel</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>