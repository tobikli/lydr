<?php

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('impressum.html');
    exit();
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | about</title>
    <link rel="icon" type="image/x-icon" href="/lydr_w.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: radial-gradient(
                900px circle at 200px 200px,
                rgba(29, 78, 216, 0.15),
                transparent 80%
              ),rgba(17, 23, 41, 1);
            font-family: 'Courier New', Courier, monospace;
            overflow-x: hidden;
        }

        header, footer {
            width: 100%;
            box-sizing: border-box;
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
            background: rgba(17, 23, 41, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 2;
        }
        
        a {
            color: white;

        }

        .logo {
            width: 50px;
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
            justify-content: center;
            align-items: center;
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
            font-family: 'Courier New', Courier, monospace;
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
        }
        
        footer ul li a:hover, footer ul li button:hover {
            text-decoration: underline;
        }

        form {
            display: inline;
            margin: 0;
            padding: 0px 0px 10px 0px;

        }

        form input[type="submit"] {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font: inherit; /* Inherit the font from the parent */
            text-decoration: none;
            padding: 0;
            margin: 5px;
            display: inline;
            line-height: normal;
        }

        form input[type="submit"]:hover {
            text-decoration: underline;
        }

        .hidden {
            display: none;
        }

        #moreLinks {
            margin-top: 10px;
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
        <p>Tobias Wen Klingenberg</p>
        <p>© MMXXIV</p>
        <p>Shoutout to <a style="color:white;" href="https://chatgpt.com">ChatGPT</a></p>
        <span style="white-space: nowrap">

        <a href="mailto:tobikli@pm.me" style="color: white;"><p>tobikli@pm.me</p></a>
        <a href="mailto:a@lydr.io" style="color: white;"><p>a@lydr.io</p></a>
        <br>
        <form action="/contact" method="">
            <input type="submit" value="Send me a message">
        </form>   <br><br>
        <form action="" method="post">
            <input type="submit" value="Impressum">
        </form>

        </span>

    </main>
    <footer>
        <ul>
            <li><a href="/">Home</a></li>
        </ul>
        
    </footer>
        
</body>
</html>
HTML;

echo $html;



?>