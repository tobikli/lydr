<?php
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["login"] != 1) {
    header('Location: /login?red=/profile');
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

$mailmsg = '';
$verify = '';
if ($verified == 0){
    $mailmsg = '';
    $verify = '
    <div class="verify-box">
        <p>Email not verified!</p>
        <div class="container">
            <form method="POST" class="link-input" action="check.php">
                <input type="hidden" name="email" value="'. htmlspecialchars($mail, ENT_QUOTES, 'UTF-8') .'">
                <button type="submit" name="submit">Verify Email</button>
            </form>
        </div>
    </div>
    ';
}

$confirm = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["req"])) {
    $r = trim($_POST["req"]);

    if (strcmp($r, "delete") == 0) {
        $confirm = '
        <div class="verify-box1">
        <p>Confirm</p>
        <div class="container">
            <form method="POST" class="link-input" action="delete.php">
                <button  type="submit" name="submit" value="true"">Yes</button>
                <button type="submit" name="submit" value="false">No</button>
            </form>
        </div>
    </div>';
    } 
}

if ($status == "admin"){
    $status = "<p><a style=\"color:white;\"href=\"/admin\">admin</a></p>";
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
    <link rel="icon" type="image/x-icon" href="/lydr_w.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            background-position: 70% 60%; */
            background: rgba(17, 23, 41, 1); /* Fallback color */
            background: radial-gradient(
                900px circle at 200px 200px,
                rgba(29, 78, 216, 0.15),
                transparent 80%
              ),rgba(17, 23, 41, 1);
            font-family: 'Courier New', Courier, monospace;
            overflow-x: hidden; /* Prevent horizontal scrolling */
        }

        header, footer {
            width: 100%;
            box-sizing: border-box; /* Ensure header and footer stay within viewport */
        }
        
        .header {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(17, 23, 41, 0.8); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Apply Gaussian blur */
            -webkit-backdrop-filter: blur(10px); /* Safari support */
            z-index: 2; /* Ensure header is above main content */
        }
        
        .container{
          display:flex;
          flex-direction: column;
          width: 100%;
          justify-content: center;
          align-items: center;
        }
        a {
            color: white;
        }

        .logo {
            width: 50px; /* Logo size */
            height: auto;
            margin-right: 20px;
        }

        .logo-text {
            color: white;
            font-size: 1.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            padding: 10px;
        }
        
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center; /* Center items horizontally */
            justify-content: center; /* Center items vertically if needed */
            flex-grow: 1;
            padding: 90px 10px 40px; 
            color: white;
            font-size: 1em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            text-align: center; /* Ensure text is centered */
        }
        
        .verify-box1 button[name="submit"][value="true"]:hover {
            background-color: rgba(138, 26, 58, 0.8);
        }
        
        .verify-box1 button[name="submit"] {
            margin-top: 5px;
        }
        
        .link-input button{
            background-color: rgba(12, 17, 31, 0.4);
            padding: 10px 20px;
            font-size: 1em;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }
        
        .link-input button:hover{
            background-color: rgba(12, 17, 31, 0.8);
        }
        
        .link-delete button{
            background-color: rgba(138, 26, 58, 0.4);
            padding: 10px 20px;
            font-size: 1em;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }
        
        .link-delete button:hover{
            background-color: rgba(138, 26, 58, 0.8);
        }
        
    
        footer {
            width: 100%;
            text-align: center;
            padding: 10px 0;
            position: relative;
            bottom: 0;
            background: rgba(17, 23, 41, 0.8); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Apply Gaussian blur */
            -webkit-backdrop-filter: blur(10px); /* Safari support */
            z-index: 2; /* Ensure header is above main content */
        }

        button {
            font-family: 'Courier New', Courier, monospace; /* Default font, can be changed */
        }
        
        .verify-box1 {
            border: 0px dashed #ccc;
            padding: 15px;
            margin: 20px auto; /* Center the box horizontally */
            border-radius: 2px;
            background-color: rgba(17, 23, 41, 0.2); /* Adjusted for better visibility */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Optional: adds shadow for better visibility */
            width: 200px; /* Fixed width to ensure consistent centering */
            max-width: 600px; /* Maximum width */
            box-sizing: border-box;
            text-align: center; /* Center text inside the box */
        }
        
        .verify-box1 .container {
            display: flex;
            flex-direction: column; /* Stack buttons vertically */
            justify-content: center; /* Center buttons horizontally */
            align-items: center; /* Center buttons vertically */
        }
        
        .verify-box1 button {
            margin: 10px 0; /* Add margin to space out buttons */
        }


        .verify-box {
            border: 0px dashed #ccc;
            padding: 15px;
            margin: 20px auto; /* Center the box horizontally */
            border-radius: 2px;
            background-color: rgba(17, 23, 41, 0.2); /* Adjusted for better visibility */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Optional: adds shadow for better visibility */
            width: 300px; /* Fixed width to ensure consistent centering */
            max-width: 600px; /* Maximum width */
            box-sizing: border-box;
            margin-left: auto;
            margin-right: auto;
            text-align: center; /* Center text inside the box */
        }
        
        .verify-box p {
            margin: 0;
            font-weight: none;
            color: white;
        }
        
        .verify-box .container {
            display: flex;
            justify-content: center; /* Center the form horizontally */
        }
        
        .verify-box form {
            margin-top: 20px;
            display: flex; /* Use flexbox to center the button */
            justify-content: center; /* Center the button horizontally */
            align-items: center; /* Center the button vertically if needed */
        }
        
        .verify-box button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: rgba(12, 17, 31, 0.4);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: auto; /* Allow button to resize based on its content */
            box-sizing: border-box;
        }
        
        .verify-box button:hover {
            background-color: rgba(12, 17, 31, 0.8);
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

        
       
        .verify-box {
            border: 0px dashed #ccc;
            padding: 15px;
            margin: 20px auto; /* Center the box horizontally */
            border-radius: 2px;
            background-color: rgba(17, 23, 41, 0.2); /* Adjusted for better visibility */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Optional: adds shadow for better visibility */
            width: 300px; /* Fixed width to ensure consistent centering */
            max-width: 600px; /* Maximum width */
            box-sizing: border-box;
            text-align: center; /* Center text inside the box */
        }
        
    


        @media (min-width: 800px) {
            .main-content {
                padding: 110px 10px 40px; 
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
        <div>
        <form method="" class="link-input" action="/profile/change">
            <div>
                <button type=submit name=submit>Change Email</button>
            </div>
        </form>
        <form method="" class="link-input" action="/login/reset">
            <div>
                <button type=submit name=submit>Change Password</button>
            </div>
        </form>
        <form method="" class="link-input" action="/logout">
            <div>
                <button style="color: #c94138;" type=submit name=submit>Logout</button>
            </div>
        </form>
        <br>
    </div>
        $verify
        <br>
        $msg
        <div class="container">
        <form method="POST" class="link-delete" action="">
            <div>
                <input type="hidden" name="req" value="delete">
                <button type=submit name=submit >Delete Account</button>
            </div>
        </form>
        $confirm
        </div>

    </main>
    <footer>
        <ul>
            <li><a href="https://lydr.io">Home</a></li>
            <li><a href="https://lydr.io/about">About</a></li>
        </ul>
    </footer>
</body>
</html>
HTML;

// Output the HTML content
echo $html;
?>