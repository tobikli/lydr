<?php

// Path to the users.json file in the parent directory
$usersFile = __DIR__ . '/../../users.json';
$keysFile = __DIR__ . '/keys.json';

// Load users from the JSON file
if (file_exists($usersFile)) {
    $usersData = json_decode(file_get_contents($usersFile), true);
} else {
    die("User data file not found.");
}

// Load keys from the keys.json file (create the file if it doesn't exist)
if (file_exists($keysFile)) {
    $keysData = json_decode(file_get_contents($keysFile), true);
} else {
    $keysData = []; // Initialize as an empty array if the file doesn't exist
}

if (isset($_POST["email"])) {
    $email = strtolower(trim($_POST["email"])); // Normalize email input
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /login/reset?error=Invalid Email.");
        exit();
    }
    
    // Remove any existing entry with the same email from keysData
    foreach ($keysData as $index => $entry) {
        if ($entry["email"] === $email) {
            unset($keysData[$index]); // Remove the existing entry
            break; // No need to continue searching
        }
    }

    // Search for the user in the JSON data
    foreach ($usersData as $user) {
        if ($user["email"] === $email) {
            // User found, generate a random key
            $randomKey = bin2hex(random_bytes(16)); // 32-character hexadecimal key
            
            // Prepare the new entry
            $newEntry = [
                "email" => $email,
                "key" => $randomKey,
                "created_at" => date('Y-m-d H:i:s') // Timestamp for reference
            ];
            
            // Add the new entry to the keysData array
            $keysData[] = $newEntry;

            // Save the updated keysData array back to keys.json
            if (file_put_contents($keysFile, json_encode($keysData, JSON_PRETTY_PRINT))) {
                // Prepare the reset link
                $resetLink = "https://lydr.io/login/reset/verify/$randomKey";

                // Prepare the email message
                $subject = "Password Reset Request";
                $message = "Hi,\n\nYou requested a password reset. Click the link below to reset your password:\n\n$resetLink\n\nIf you did not request this, please ignore this email.";
                $headers = "From: no-reply@lydr.io";

                // Send the email
                if (mail($email, $subject, $message, $headers)) {
                    // Email sent successfully
                    header("Location: /login/reset?message=If Email is registered, a link has been sent.");
                    exit();
                } else {
                    // Error sending email
                    header("Location: /login/reset?error=Failed to send email. Please try again.");
                    exit();
                }
            } else {
                // Error writing to the keys.json file
                header("Location: /login/reset?error=An error occurred. Please try again.");
                exit();
            }
        }
    }

    // Regardless of whether the user was found, show the same message for security reasons
    header("Location: /login/reset?message=If Email is registered, a link has been sent.");
    exit();
}

// If no POST data, redirect to reset page
header("Location: /login/reset");
exit();

?>