<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $data = json_encode([
        "username" => $username,
        "password" => $password
    ]);

    $ch = curl_init("http://203.94.72.18/trainee/api/auth/signin");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);

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
<head><title>Login</title></head>
<body>

<h2>Login</h2>

<?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>

<form method="POST" action="login.php">
    NIC: <input type="text" name="username"><br><br>
    Password: <input type="password" name="password"><br><br>
    <button type="submit">Login</button>
</form>

</body>
</html>