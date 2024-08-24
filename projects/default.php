<?php

$html=<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | projects</title>
    <link rel="icon" type="image/x-icon" href="https://lydr.io/lydr.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: rgb(9, 9, 28); /* Gradient background */
            font-family: 'Courier New', Courier, monospace;
        }

        .header {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px 0;
            position: fixed; /* Keep header fixed at the top */
            top: 0;
            left: 0;
            background: rgb(5,5,25); /* Add background to header */
            z-index: 2; /* Ensure header is above main content */
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
            flex-direction: column;
            text-align: center;
            flex-grow: 1;
            padding: 100px 20px 60px; /* Adjusted padding for header and footer */
            color: white;
            font-size: 1em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            width: 100%;
            box-sizing: border-box; /* Include padding in element's width and height */
        }

        footer {
            width: 100%;
            text-align: center;
            padding: 20px 0;
            z-index: 2; /* Ensure footer is above main content */
            background: rgb(5,5,25);
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

        footer ul li a:hover {
            text-decoration: underline;
        }
        
        button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: #131329;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            max-width: 150px;
            box-sizing: border-box;
        }

        button:hover {
            background-color: #001f43;
        }

        .project-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
            max-width: 1200px; /* Max width of the container */
            margin-top: 20px;
        }

        .project-box {
            background-color: #131329;
            color: white;
            padding: 20px;
            margin: 10px;
            flex: 1 1 calc(33.333% - 40px); /* Adjust width of the boxes */
            box-sizing: border-box;
            border: 1px solid #001124;
            border-radius: 5px;
            min-width: 250px; /* Minimum width for a box */
            max-width: calc(33.333% - 40px); /* Max width of a box */
            text-align: left;
        }

        .project-title {
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .project-description {
            font-size: 1em;
        }

        @media (max-width: 800px) {
            .project-box {
                flex: 1 1 calc(50% - 40px); /* 2 per row on smaller screens */
            }
        }

        @media (max-width: 500px) {
            .project-box {
                flex: 1 1 100%; /* 1 per row on smallest screens */
            }
        }
    </style>

</head>
<body>
    <a href="/">
        <header class="header">
        <img src="https://lydr.io/lydr.gif" alt="Image" class="logo">
            <div class="logo-text">lydr projects</div>
        </header>
    </a>
    <main class="main-content">
        <br><br>


        <p>All current and past projects</p>
        <div class="project-container">
            <div class="project-box">
                <div class="project-title"><a href="https://go.lydr.io" style="color:white;">go.lydr</a></div>
                <div class="project-description">Simple Link shortener with possible custom link</div>
            </div>
            <div class="project-box">
                <div class="project-title"><a href="https://dl.lydr.io" style="color:white;">dl.lydr</a></div>
                <div class="project-description">File Host for all type of files</div>
            </div>
            <div class="project-box">
                <div class="project-title"><a href="/ar" style="color:white;">Edu AR</a></div>
                <div class="project-description">WebAR App for a Skeleton Model. TUM Seminar. Based on <a href="https://ar-js-org.github.io/AR.js-Docs/" style="color:white;">ar.js</a></div>
            </div>
            <div class="project-box">
                <div class="project-title"><a href="https://tace.app" style="color:white;">Tace</a></div>
                <div class="project-description">Flutter based App. In Dev.</div>
            </div>
            <div class="project-box">
                <div class="project-title"><a href="https://github.com/tobikli/imgtoascii" style="color:white;">imgtoascii</a></div>
                <div class="project-description">Simple image to asciiart converter in python.</div>
            </div>
            <div class="project-box">
                <div class="project-title"><a href="https://github.com/tobikli/lydr" style="color:white;">lydr</a></div>
                <div class="project-description">This website.</div>
            </div>
        </div>
            <a href="https://github.com/tobikli/"><button type="button"><img style="width:40px;" src="/images/github.png"/></button></a>

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
