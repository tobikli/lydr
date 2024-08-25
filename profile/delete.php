<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION["login"]) || $_SESSION["login"] != 1) {
    header('Location: /login?red=/profile');
    exit;
}

// Path to the users.json file in the parent directory
$usersFile = __DIR__ . '/../users.json';

// Load users from the JSON file
if (file_exists($usersFile)) {
    $usersData = json_decode(file_get_contents($usersFile), true);
} else {
    // If the users.json file is missing, handle the error appropriately
    die("User data file not found.");
}

// Get the current user's ID from the session
$userid = $_SESSION["userid"];

// Get form data
if (isset($_POST["submit"])) {
    $conf = strtolower(trim($_POST["submit"]));
  
    // Validate input (simple validation)
    if (strcmp($conf, "true") == 0) {
        // Remove the user from the array
        $usersData = array_filter($usersData, function($user) use ($userid) {
            return $user['userid'] != $userid;
        });

        // Save the updated user data back to the JSON file
        if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT))) {
            // Log the user out
            session_destroy();
            // Redirect to the logout page
            header("Location: /logout");
            exit();
        } else {
            die("Failed to save changes to user data file.");
        }
    } else {
        header("Location: /profile");
        exit();
    }
} else {
    // If no POST data, redirect to the profile page
    header("Location: /profile");
    exit();
}
?>
