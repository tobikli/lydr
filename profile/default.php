<?php
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["login"] != 1) {
    header('Location: /login');
    exit;
}

$userid = $_SESSION["userid"];
$format = sprintf("%06d", $userid);
$username = '';
$verified = '';
$mail = '';
$status = '';

$usersFile = __DIR__ . '/../users.json';

if (file_exists($usersFile)) {
    $usersData = json_decode(file_get_contents($usersFile), true);
} else {
    die("User data file not found.");
}

foreach ($usersData as $user) {
    if ($user['userid'] == $userid) {
        $username = $user['username'];
        $verified = $user['verified']; 
        $mail = $user['email']; 
        $status = $user['status'];
        break;
    }
}

$verify = '';
if ($verified == 0){
    $verify = '
    <form method="POST" class="link-input" action="check.php">
    <div>
        <input type="hidden" name="email" value="'. $mail .'">
        <button type=submit name=submit >Verify Email</button>
        </div>
    </form>
    ';
}

$adcont = '';
if ($userid == 1){
    $adcont = "<a style=\"color:white;\"href=\"/admin\">Admin Panel</a>";
}

$msg = '';
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']); // Use htmlspecialchars to avoid XSS attacks
}

// Path to the name.txt file
$nameFile = __DIR__ . '/l/' . $userid . '/name.txt';

// Initialize variables
$filename = '';

// Check if the file exists and read its contents
if (file_exists($nameFile)) {
    $lines = file($nameFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Ensure there are exactly 3 lines
    if (count($lines) >= 0) {
        $filename = trim($lines[0]);
    }
}

// Ensure values are safe to use in HTML
$filename = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');

// User is logged in
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | profile</title>
    <link rel="icon" type="image/x-icon" href="https://lydr.io/lydr.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            background: rgb(9, 9, 28); /* Gradient background */
            font-family: 'Courier New', Courier, monospace;
        }

        .header {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px 0;
            position: absolute;
            background: rgb(5,5,25); /* Add background to header */
            top: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .logo {
            width: 50px; /* Adjust size as needed */
            height: auto;
            margin-right: 20px;
        }

        .logo-text {
            color: white;
            font-size: 1.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column; /* Added to stack items vertically */
            text-align: center;
            flex-grow: 1;
            padding: 20px;
            color: white;
            font-size: 1em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        footer {
            width: 100%;
            text-align: center;
            padding: 20px 0;
            position: absolute;
            bottom: 0;
        }

        button {
            font-family: 'Courier New', Courier, monospace; /* Default font, can be changed */
        }

        footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        footer ul li {
            display: inline;
            margin: 0 10px;
        }

        footer ul li a, footer ul li button {
            color: white;
            text-decoration: none;
            font-size: 1em;
            background: none;
            border: none;
            cursor: pointer;
        }

        footer ul li a:hover, footer ul li button:hover {
            text-decoration: underline;
        }

        .hidden {
            display: none;
        }

        .link-input {
            margin-top: 20px;
            display: flex;
            align-items: center;
            flex-direction: column;
            width: 100%;
            max-width: 800px; /* Increased default width */
        }

        .link-input input[type="text"] {
            padding: 10px;
            font-size: 1em;
            border: 0px solid #ccc;
            border-radius: 0px;
            width: 100%;
            background-color: #131329; /* Dark background */
            color: white; /* White text */
            font-family: 'Courier New', Courier, monospace;
            box-sizing: border-box;
        }

         .link-input button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: #001124;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            box-sizing: border-box;
        }

        .link-input button:hover {
            background-color: #001f43;
        }

        .shortened-url {
            margin-top: 10px;
            color: white;
            font-family: 'Courier New', Courier, monospace;
        }

        .shortened-url a {
            color: white;
            text-decoration: underline;
        }

        .shortened-url a:hover {
            text-decoration: none;
        }
        input {
            display: block;
        }

        @media (min-width: 800px) {
            .link-input {
                flex-direction: row;
            }

            .link-input button {
                width: auto;
                margin-top: 0;
                margin-left: 10px;
            }
        }

        .upload-container {
            cursor: pointer;
            display: inline-block;
            position: relative;
        }

        .upload-container input[type="file"] {
            opacity: 0;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
    </style>
    <script>
        function submitFormAfterFileSelection(inputElement) {
            const form = inputElement.closest('form');
            if (inputElement.files.length > 0) {
                form.submit();  // Only submit if a file is selected
            }
        }
    </script>
</head>
<body>
    <a href="/">
    <header class="header">
        <img src="https://lydr.io/lydr.gif" alt="Image" class="logo">
        <div class="logo-text">lydr profile</div>
    </header>
    </a>
    <main class="main-content">
       <form method="POST" enctype="multipart/form-data" action="upload.php"> 
        <div class="upload-container">
            <img src="https://lydr.io/profile/l/$userid/$filename" style="height:80px; width:80px; object-fit: cover; border-radius: 50%; border: 1px solid #000000;" alt="Profile Image">
            <input type="file" name="profile_image" onchange="submitFormAfterFileSelection(this)">
        </div>
    </form>
        <p>#$format</p>
        $status
        <h2>$username</h2>
       <a>$mail</a>
        <br>
        <p>$adcont</p>
        <div>$verify</div>
        <br>
        $msg
        <br>
    </main>
    <footer>
        <ul>
            <li><a href="https://lydr.io">Home</a></li>
            <li><a href="https://lydr.io/logout">Logout</a></li>
            <li><a href="https://lydr.io/about">About</a></li>
        </ul>
    </footer>
</body>
</html>
HTML;

// Output the HTML content
echo $html;
?>