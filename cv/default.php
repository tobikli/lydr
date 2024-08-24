<?php

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | cv</title>
    <link rel="icon" type="image/x-icon" href="https://lydr.io/lydr.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: rgb(9, 9, 28); /* Background color */
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
            background: rgb(5,5,25); /* Background for header */
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
        }

        .main-content {
            flex: 1; /* Allows content to fill remaining space */
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 120px 20px 60px; /* Padding for header and footer */
            color: white;
            font-size: 1em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            width: 100%;
            box-sizing: border-box; /* Include padding in element's width and height */
        }

        embed {
            max-width: 100vw;
            width: 700px;
            height: 100%;
            border: none;
            margin-top: 10px;
        }

        footer {
            text-align: center;
            padding: 20px 0;
            background: rgb(5,5,25);
            box-sizing: border-box;
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
        }

        ul {
            list-style-type: none; /* Remove bullets */
            padding: 0; /* Remove padding */
            margin: 0; /* Remove margins */
        }

        li {
            padding: 2px;
        }

        h1, h2, h3, h4, h5, h6, h7 {
            font-weight: normal;
        }

        footer ul li a:hover {
            text-decoration: underline;
        }

        /* Mobile view adjustments */
        @media (max-width: 800px) {
            .main-content {
                padding: 100px 10px 40px;
            }

            embed {
                height: 500px; /* Adjust iframe height for smaller screens */
            }
        }

    </style>

</head>
<body>
    <a href="/">
    <header class="header">
        <img src="https://lydr.io/lydr.gif" alt="Image" class="logo">
            <div class="logo-text">lydr cv</div>
    </header>
    </a>

    <main class="main-content">
        <embed src="the_cv.pdf" type="application/pdf" width="100%" height="100%" style="max-width: 100vw; height: 80vh; background-color: white;">
    </main>

    <footer>
        <ul>
            <li><a href="https://lydr.io/about">About</a></li>
        </ul>
    </footer>
</body>
</html>
HTML;

echo $html;
?>
