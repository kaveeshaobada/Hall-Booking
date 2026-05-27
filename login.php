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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_encode([
        "username" => $_POST["username"],
        "password" => $_POST["password"]
    ]);

    $ch = curl_init("http://203.94.72.18/trainee/api/auth/signin");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    handleApiError($httpCode, $response);
    checkToken($httpCode);
    $result = json_decode($response, true);

    if (isset($result["token"])) {
        $_SESSION["token"] = $result["token"];
        $_SESSION["userId"] = $result["userId"];
        $_SESSION["username"] = $result["username"];
        header("Location: index.php");
        exit();
    } else {
        $error = "Login failed. Check your credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh;">

<div class="card">
    <h2 style="margin-top:0">Hall Booking System</h2>
    <p style="color:#666">Ministry of Finance Login</p>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (isset($_GET["expired"])) echo "<p style='color:orange'>Session expired. Please login again.</p>"; ?>

    <form method="POST" action="login.php">
        <label>NIC Number</label>
        <input type="text" name="username" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <br>
        <button class="btn btn-primary" type="submit" style="width:100%; padding:10px;">Login</button>
    </form>
</div>

</body>
</html>