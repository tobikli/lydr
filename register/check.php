<?php
session_start();

// Path to the users.json file in the parent directory
$usersFile = __DIR__ . '/../users.json';

$keysFile = __DIR__ . '/keys.json';

if (file_exists($keysFile)) {
    $keysData = json_decode(file_get_contents($keysFile), true);
} else {
    $keysData = []; // Initialize as an empty array if the file doesn't exist
}


// Load users from the JSON file
if (file_exists($usersFile)) {
    $usersData = json_decode(file_get_contents($usersFile), true);
} else {
    // If the users.json file is missing, handle the error appropriately
    die("User data file not found.");
}

// Get form data
if (isset($_POST["username"]) && isset($_POST["email"]) && isset($_POST["password"])) {
    $username = trim($_POST["username"]);
    $email = strtolower(trim($_POST["email"]));
    $password = trim($_POST["password"]);
    $username = strtolower($username);

    // Validate input (simple validation)
    if (empty($username) || empty($email) || empty($password)) {
        header("Location: /register?error=Please fill in all fields.");
        exit();
    }

    // Check if the username or email already exists
    foreach ($usersData as $user) {
        if ($user["username"] === $username) {
            header("Location: /register?error=Username already taken.");
            exit();
        }
        if ($user["email"] === $email) {
            header("Location: /register?error=Email already registered.");
            exit();
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /register?error=Invalid Email.");
            exit();
        }
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Create a new user ID (find the maximum existing ID and add 1)
    $newUserId = 1;
    if (!empty($usersData)) {
        $newUserId = max(array_column($usersData, 'userid')) + 1;
    }
    
    $path = '/../profile/l/' . $newUserId;
    
    $directoryPath = __DIR__ . $path;
    mkdir($directoryPath, 0777, true);

    $exampleFilePath = __DIR__ . '/../profile/placehold.png';
            $defaultFilePath = $directoryPath . '/placehold.png';

            // Check if example.php exists and copy it to default.php
            if (file_exists($exampleFilePath)) {
                if (!copy($exampleFilePath, $defaultFilePath)) {
                    die('Failed to copy example.php to default.php');
                }
            } else {
                die('example.php not found');
            }
    $fileDetails = "placehold.png\n";
            file_put_contents($directoryPath . '/name.txt', $fileDetails);
            chmod($directoryPath . '/name.txt', 0640);

    // Create a new user array
    $newUser = array(
        "userid" => $newUserId,
        "username" => $username,
        "password" => $hashedPassword,
        "email" => $email,
        "verified" => 0,
        "status" => "member"
    );

    // Add the new user to the usersData array
    $usersData[] = $newUser;

    // Save the updated user data back to the JSON file
    if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT))) {
        
        $key = bin2hex(random_bytes(16)); // 32-character hexadecimal key
            
            // Prepare the new entry
            $newEntry = [
                "email" => $email,
                "key" => $key,
                "created_at" => date('Y-m-d H:i:s') // Timestamp for reference
            ];
            
            // Add the new entry to the keysData array
            $keysData[] = $newEntry;

            // Save the updated keysData array back to keys.json
            file_put_contents($keysFile, json_encode($keysData, JSON_PRETTY_PRINT));
        // Registration successful, redirect to login page or auto-login
                $link = "https://lydr.io/register/verify/" . $key;
                $subject = "Account Registration";
                $message = "Hi,\n\nYou created an Account on lydr.io. Click the link below to verify your Email:\n\n$link\n\nIf you did not request this, please ignore this email.";
                $headers = "From: no-reply@lydr.io";

                // Send the email
                if (mail($email, $subject, $message, $headers)) 

        header("Location: /login");
        exit();
    } else {
        // Error saving user data
        header("Location: /register?error=Unable to register. Please try again.");
        exit();
    }
}

// If no POST data, redirect to the registration form
header("Location: /register");
exit();

?>