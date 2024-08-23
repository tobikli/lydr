<?php
session_start();

// Path to the users.json file in the parent directory
$usersFile = __DIR__ . '/../users.json';


// Load users from the JSON file
if (file_exists($usersFile)) {
    $usersData = json_decode(file_get_contents($usersFile), true);
} else {
    // If the users.json file is missing, handle the error appropriately
    die("User data file not found.");
}


if (isset($_POST["loginname"]) && isset($_POST["loginpasswort"])) {
    $loginname = $_POST["loginname"];
    $loginpasswort = $_POST["loginpasswort"];
    $loginname = strtolower($loginname);

    $redirect = '';
   
    if (isset($_POST["red"])){
         $redirect = $_POST["red"];
    }
    
    if (!filter_var($redirect, FILTER_VALIDATE_URL)) { 
    //$redirect = '';
    }
    // Search for the user in the JSON data
    foreach ($usersData as $user) {
        if ($user["username"] === $loginname) {
            // Verify the password using password_verify
            if (password_verify($loginpasswort, $user["password"])) {
                // Correct login details - set session and redirect
                $_SESSION["login"] = 1;
                $_SESSION["userid"] = $user["userid"];   // Store userid in session
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["status"] = $user["status"];
                
                        
                if($redirect != ""){
                header("Location: " . $redirect);
                exit();
                }
                header("Location: /profile");
                exit();
            } else {
                // Incorrect password
                header("Location: /login?error=wrong password");
                exit();
            }
        }
    }

    // If no matching user found
    header("Location: /login?error=user not found");
    exit();
}

// If no POST data, redirect to login
header("Location: /login");
exit();

?>