<?php
session_start();
if (!isset($_SESSION["token"])) {
    header("Location: login.php");
    exit();
}

$token = $_SESSION["token"];

$ch = curl_init("http://203.94.72.18/trainee/api/production/hall/get/all/active");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$response = curl_exec($ch);

$halls = json_decode($response, true);
?>

<!DOCTYPE html>
<html>
<head><title>Hall Booking System</title></head>
<body>

<h2>Welcome, <?php echo $_SESSION["username"]; ?></h2>
<a href="add_hall.php">+ Add New Hall</a> | 
<a href="bookings.php">View Bookings</a> | 
<a href="logout.php">Logout</a>

<h3>All Halls</h3>

<?php if (empty($halls)): ?>
    <p>No halls found. Add one!</p>
<?php else: ?>
<table border="1" cellpadding="8">
    <tr>
        <th>Name</th>
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
        <td><?php echo $hall["location"]; ?></td>
        <td><?php echo $hall["capacity"]; ?></td>
        <td><?php echo $hall["hasProjector"] ? "Yes" : "No"; ?></td>
        <td><?php echo $hall["hasAc"] ? "Yes" : "No"; ?></td>
        <td><?php echo $hall["hasWhiteboard"] ? "Yes" : "No"; ?></td>
        <td>
            <a href="add_booking.php?hallId=<?php echo $hall["id"]; ?>">Book</a> |
            <a href="update_hall.php?id=<?php echo $hall["id"]; ?>">Edit</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>