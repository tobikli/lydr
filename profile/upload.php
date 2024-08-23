<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header('Location: /login');
    exit();
}

$userid = $_SESSION["userid"];

// Define paths
$targetDirectory = __DIR__ . '/l/' . $userid . "/";
$nameFile = $targetDirectory . 'name.txt';

// Initialize variables
$filename1 = '';

// Check if the name.txt file exists and read its contents
if (file_exists($nameFile)) {
    $lines = file($nameFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (count($lines) > 0) {
        $filename1 = trim($lines[0]);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    
    // Check if there was an error during file upload
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $file['tmp_name'];

        // Get original file name and extension
        $originalFileName = pathinfo($file['name'], PATHINFO_FILENAME);
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Create new file name by appending "1" to the original file name
        $newFileName = $originalFileName . '1.' . $fileExtension;

        // Set the full path where the file will be saved
        $destinationPath = $targetDirectory . $newFileName;
        
        // Check file type (optional)
        $allowedFileTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($fileTmpPath);
        
        if (in_array($fileType, $allowedFileTypes)) {
            // Move the file to the target directory
            if (move_uploaded_file($fileTmpPath, $destinationPath)) {
                // Delete the old profile image and name.txt if they exist
                if (!empty($filename1) && file_exists($targetDirectory . $filename1)) {
                    unlink($targetDirectory . $filename1);
                }
                if (file_exists($nameFile)) {
                    unlink($nameFile);
                }
                
                // Save the new file name to name.txt
                file_put_contents($nameFile, $newFileName);
                chmod($nameFile, 0640);

                // File successfully uploaded, redirect back to profile
                header("Location: /profile?msg=Profile image updated.");
                exit();
            } else {
                // Failed to move the file
                header("Location: /profile?error=Failed to move the uploaded file.");
                exit();
            }
        } else {
            // Invalid file type
            header("Location: /profile?error=Invalid file type. Only JPG, PNG, and WebP are allowed.");
            exit();
        }
    } else {
        // Handle file upload errors
        header("Location: /profile?error=File upload error.");
        exit();
    }
} else {
    // No file uploaded
    header("Location: /profile?error=No file uploaded.");
    exit();
}

?>