<?php
session_start();

if (isset($_GET['key'])) {
    $key = $_GET['key'];
} else {
    // If no key is present, redirect to an appropriate page (e.g., the reset page)
    header("Location: /register");
    exit();
}


$keysFile = __DIR__ . '/../keys.json';
$usersFile = __DIR__ . '/../../users.json';

$error = '';
$msg = '';

if (file_exists($keysFile)) {
    $keysData = json_decode(file_get_contents($keysFile), true);
} else {
    die("Keys file not found.");
}

if (file_exists($usersFile)) {
    $usersData = json_decode(file_get_contents($usersFile), true);
} else {
    die("Users file not found.");
}

$validKey = false;
$email = '';

foreach ($keysData as $index => $entry) {
    if ($entry['key'] === $key) {
        $validKey = true;
        $email = $entry['email'];  // Store the associated email for later use
        break;
    }
}

if (!$validKey) {
    header("Location: /register");
    exit();
}


    
        $userFound = false;
        foreach ($usersData as &$user) {
            if ($user['email'] === $email) {
                $user['verified'] = 1;
                $userFound = true;
                break;
            }
        }

        if ($userFound) { 
            // Save the updated users.json
            if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT))) {
                // Remove the key from keys.json
                foreach ($keysData as $index => $entry) {
                    if ($entry['key'] === $key) {
                        unset($keysData[$index]);
                        break;
                    }
                }
                // Save the updated keys.json
                file_put_contents($keysFile, json_encode(array_values($keysData), JSON_PRETTY_PRINT));

                $msg = "Email verified.";
            } else {
                $error = "Please try again.";
            }}
            
            
            
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | verify</title>
    <link rel="icon" type="image/x-icon" href="/lydr_w.png">
    <style>
       body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            /* background-image: url('/images/bg.png');
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
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            flex-grow: 1;
            padding: 100px 20px 60px; /* Adjusted padding for header and footer */
            color: white;
            font-size: 1em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            width: 100%;
            box-sizing: border-box; /* Include padding in element's width and height */
            z-index: 1; /* Ensure main content is below header */
            overflow: hidden; /* Prevent scrolling on the main content */
        }
        
        footer {
            width: 100%;
            text-align: center;
            padding: 20px 0;
            position: absolute;
            bottom: 0;
            z-index: 2; /* Ensure footer is above main content */
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
            display:block;
            align-items: center;
            flex-direction: column;
            width: 100%;
            max-width: 800px; /* Increased default width */
        }
        
        .link-input input[type="password"] {
            padding: 10px;
            font-size: 1em;
            border: 0px solid #ccc;
            border-radius: 0px;
            width: 100%;
            background-color: #131329; /* Dark background */
            color: white; /* White text */
            font-family: 'Courier New', Courier, monospace;
            box-sizing: border-box;
            margin-top: 10px;

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
    </style>

</head>
<body>
    <a href="/">
    <header class="header">
            <div class="logo-text">lydr verify</div>
    </header>
    </a>
    <main class="main-content">



        <br>
        <span style="color:red;">$error</span>
        <span style="color:white;">$msg</span>
        <br> 
        <a href="/login" style="color: white; text-decoration: underline dashed">Login</a>

      
        
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

echo $html;
?>
        
        