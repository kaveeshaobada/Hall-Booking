<?php
function handleApiError($httpCode, $response) {
    switch ($httpCode) {
        case 401:
            session_destroy();
            header("Location: login.php?expired=1");
            exit();
        case 403:
            die("<p style='color:red'>Error: You don't have permission to do this.</p>");
        case 404:
            die("<p style='color:red'>Error: Resource not found.</p>");
        case 500:
            die("<p style='color:red'>Error: Server error. Please try again.</p>");
    }
}
?>