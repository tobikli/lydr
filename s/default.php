<?php


// Default HTML content
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | iOS</title>
    <link rel="icon" type="image/x-icon" href="/lydr.png">
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
            <div class="logo-text">lydr</div>
    </header>
    </a>
    <main class="main-content">
        <h2>iOS</h2>

    </main>
    <footer>
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/about">About</a></li>

        </ul>
    </footer>
</body>
</html>
HTML;

// Output the HTML content
echo $html;
?>
