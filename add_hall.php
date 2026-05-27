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
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_encode([
        "name"          => $_POST["name"],
        "description"   => $_POST["description"],
        "location"      => $_POST["location"],
        "capacity"      => (int)$_POST["capacity"],
        "hasProjector"  => isset($_POST["hasProjector"]) ? true : false,
        "hasAc"         => isset($_POST["hasAc"]) ? true : false,
        "hasWhiteboard" => isset($_POST["hasWhiteboard"]) ? true : false
    ]);

    $ch = curl_init("http://203.94.72.18/trainee/api/production/hall/save");
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
        header("Location: index.php");
        exit();
    } else {
        $error = "Failed: " . $response;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Hall</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Add New Hall</h2>
<a href="index.php">Back</a><br><br>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

<div class="container">
    <div class="card">
        <h2>Add New Hall</h2>
        <form method="POST" action="add_hall.php">
            Name: <input type="text" name="name" required><br><br>
            Description: <input type="text" name="description" required><br><br>
            Location: <input type="text" name="location" required><br><br>
            Capacity: <input type="number" name="capacity" required><br><br>
            <input type="checkbox" name="hasProjector" value="1"> Projector<br><br>
            <input type="checkbox" name="hasAc" value="1"> AC<br><br>
            <input type="checkbox" name="hasWhiteboard" value="1"> Whiteboard<br><br>
            <button type="submit">Save Hall</button>
        </form>
    </div>
</div>

</body>
</html>