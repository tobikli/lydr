<?php

// Path to the name.txt file
$nameFile = 'name.txt';

// Initialize variables
$filename = '';
$uploadDate = '';
$fileSize = '';
$id = '';
$rep = '';


// Check if the file exists and read its contents
if (file_exists($nameFile)) {
    $lines = file($nameFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Ensure there are exactly 3 lines
    if (count($lines) >= 3) {
        $filename = trim($lines[0]);
        $uploadDate = trim($lines[1]);
        $fileSize = trim($lines[2]);
        $hash = trim($lines[3]);
        $id = trim($lines[4]);

    }
}

// Ensure values are safe to use in HTML
$filename = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
$uploadDate = htmlspecialchars($uploadDate, ENT_QUOTES, 'UTF-8');
$fileSize = htmlspecialchars($fileSize, ENT_QUOTES, 'UTF-8');
$hash = htmlspecialchars($hash, ENT_QUOTES, 'UTF-8');
$id = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');

$rep = "https://dl.lydr.io/report?url=" . $id;


// Generate the HTML content
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dl.lydr | $filename</title>
    <link rel="icon" type="image/x-icon" href="https://lydr.io/lydr.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: rgb(9, 9, 28); /* Gradient background */
            font-family: 'Courier New', Courier, monospace;
            justify-content: center;
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
            width: 100px; /* Adjust size as needed */
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
            max-width: 90%; /* Prevents content from exceeding the screen width */
            word-wrap: break-word; /* Ensures long words break to fit screen */
            overflow-wrap: break-word; /* Older equivalent of word-wrap */
            word-break: break-all; /* Forces break on long continuous strings */
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
            padding: 10px 20px;
            font-size: 1em;
            background-color: #001124;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            box-sizing: border-box;
            transition: background-color 0.3s ease;
        }
        
        button:hover {
            background-color: #001f43;
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
        
        footer ul li a {
            color: white;
            text-decoration: none;
            font-size: 1em;
            background: none;
            border: none;
            cursor: pointer;
        }
        
        footer ul li a:hover {
            text-decoration: underline;
        }

        .hidden {
            display: none;
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
        @media only screen and (min-width: 768px) { 
            .brnodisplay {
                display: none;
            }
        }
    </style>
</head>
<body>
    <a href="/">
    <header class="header">
            <img src="https://lydr.io/lydr.png" alt="Image" class="logo">
            <div class="logo-text">dl.lydr</div>
    </header>
    </a>
    <div class="main-content">
        <p>Name: $filename</p>
        <p>Date: $uploadDate</p>
        <p>Size: $fileSize</p>
        <p>SHA256: $hash</p>
        <a href="./$filename">
            <button>Download</button>
        </a>
        <br>        
        <br>
        <a href="$rep" style="color: white; text-decoration: underline dashed">Report</a>
        
    </div>
    <footer>
        <ul>
            <li><a href="https://lydr.io">Home</a></li>
            <li><a href="/">Upload</a></li>
            <li><a href="https://lydr.io/about">About</a></li>
        </ul>
    </footer>
</body>
</html>
HTML;

// Output the HTML content
echo $html;
?>