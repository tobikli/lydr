<?php
session_start();

// Path to the users.json file in the parent directory
$usersFile = __DIR__ . '/../users.json';
$keysFile = __DIR__ . '/keys.json';

// Load keys from the keys.json file (create the file if it doesn't exist)
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
if (isset($_POST["email"])) {
    $email = strtolower(trim($_POST["email"]));
  
    // Validate input (simple validation)
    if (empty($email)) {
        header("Location: /profile?error=Please fill in all fields.");
        exit();
    }

    // Remove any existing entry with the same email from keysData
    foreach ($keysData as $index => $entry) {
        if ($entry["email"] === $email) {
            unset($keysData[$index]); // Remove the existing entry
            break; // No need to continue searching
        }
    }

    // Generate a new key
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

    // Prepare the verification email
    $link = "https://lydr.io/profile/verify/$key";
    $subject = "Email Verification";
    $message = "Hi,\n\nClick the link below to verify your email:\n\n$link\n\nIf you did not request this, please ignore this email.";
    $headers = "From: no-reply@lydr.io";

    // Send the email
    if (mail($email, $subject, $message, $headers)) {
        // Email sent successfully, redirect to profile page
        header("Location: /profile?msg=Verification Email sent.");
        exit();
    } else {
        // Error sending email
        header("Location: /profile?error=Failed to send verification email.");
        exit();
    }
}

// If no POST data, redirect to the profile page
header("Location: /profile");
exit();

?>