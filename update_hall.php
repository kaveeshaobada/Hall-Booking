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
$hallId = $_GET["id"] ?? "";
$error = "";

// Fetch existing hall data
$ch = curl_init("http://203.94.72.18/trainee/api/production/hall/get/one/$hallId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
handleApiError($httpCode, $response);
checkToken($httpCode);
$hall = json_decode($response, true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_encode([
        "id"           => $hallId,
        "name"         => $_POST["name"],
        "description"  => $_POST["description"],
        "location"     => $_POST["location"],
        "capacity"     => (int)$_POST["capacity"],
        "hasProjector" => isset($_POST["hasProjector"]) ? true : false,
        "hasAc"        => isset($_POST["hasAc"]) ? true : false,
        "hasWhiteboard"=> isset($_POST["hasWhiteboard"]) ? true : false,
        "status"       => true
    ]);

    $ch = curl_init("http://203.94.72.18/trainee/api/production/hall/update");
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
    <title>Update Hall</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Update Hall</h2>
<a href="index.php">Back</a><br><br>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>


<div class="container">
    <div class="card">
        <h2>Update Hall</h2>
        <form method="POST" action="update_hall.php?id=<?php echo $hallId; ?>">
            Name: <input type="text" name="name" value="<?php echo $hall["name"]; ?>" required><br><br>
            Description: <input type="text" name="description" value="<?php echo $hall["description"]; ?>" required><br><br>
            Location: <input type="text" name="location" value="<?php echo $hall["location"]; ?>" required><br><br>
            Capacity: <input type="number" name="capacity" value="<?php echo $hall["capacity"]; ?>" required><br><br>
            <input type="checkbox" name="hasProjector" value="1" <?php echo $hall["hasProjector"] ? "checked" : ""; ?>> Projector<br><br>
            <input type="checkbox" name="hasAc" value="1" <?php echo $hall["hasAc"] ? "checked" : ""; ?>> AC<br><br>
            <input type="checkbox" name="hasWhiteboard" value="1" <?php echo $hall["hasWhiteboard"] ? "checked" : ""; ?>> Whiteboard<br><br>
            <button type="submit">Update Hall</button>
        </form>
    </div>
</div>

</body>
</html>