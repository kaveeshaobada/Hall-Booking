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

$ch = curl_init("http://203.94.72.18/trainee/api/production/hall/get/all/active");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
handleApiError($httpCode, $response);
checkToken($httpCode);
$halls = json_decode($response, true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hall Booking System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="navbar">
    <strong>Hall Booking System</strong>
    <div>
        <a href="add_hall.php">+ Add Hall</a>
        <a href="bookings.php">Bookings</a>
        <a href="logout.php">Logout (<?php echo $_SESSION["username"]; ?>)</a>
    </div>
</div>

<div class="container">
    <h2>All Halls</h2>

    <?php if (empty($halls)): ?>
        <p>No halls found.</p>
    <?php else: ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Location</th>
            <th>Capacity</th>
            <th>Projector</th>
            <th>AC</th>
            <th>Whiteboard</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($halls as $hall): ?>
        <tr>
            <td><?php echo $hall["name"]; ?></td>
            <td><?php echo $hall["description"]; ?></td>
            <td><?php echo $hall["location"]; ?></td>
            <td><?php echo $hall["capacity"]; ?></td>
            <td><?php echo $hall["hasProjector"] ? "✅" : "❌"; ?></td>
            <td><?php echo $hall["hasAc"] ? "✅" : "❌"; ?></td>
            <td><?php echo $hall["hasWhiteboard"] ? "✅" : "❌"; ?></td>
            <td>
                <a class="btn btn-primary" href="add_booking.php?hallId=<?php echo $hall["id"]; ?>">Book</a>
                <a class="btn btn-warning" href="update_hall.php?id=<?php echo $hall["id"]; ?>">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>

</body>
</html>