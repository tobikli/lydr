<?php
// logout.php
session_start();




session_unset(); // Clear all session variables
session_destroy(); // Destroy the session

if (isset($_GET['red'])) {
    $red = htmlspecialchars($_GET['red']);
    header("Location: ". $red);
    exit();// Redirect to the login page
}
header("Location: /login"); // Redirect to the login page
exit;
?>