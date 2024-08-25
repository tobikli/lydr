<?php

$html=<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | projects</title>
    <link rel="icon" type="image/x-icon" href="/lydr_w.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: rgba(17, 23, 41, 1);
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
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            flex-grow: 1;
            padding: 100px 20px 60px;
            color: white;
            font-size: 1em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            width: 100%;
            box-sizing: border-box;
        }

        footer {
            width: 100%;
            text-align: center;
            padding: 20px 0;
            z-index: 2;
            background:  rgba(17, 23, 41, 0.8); 
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
            background-color: rgba(27, 45, 87, 0.4);
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
            background-color: rgba(27, 45, 87, 0.2);
        }

        .project-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
            max-width: 1200px;
            margin-top: 20px;
        }

        .project-box {
            background-color: rgba(27, 45, 87, 0.4);
            color: white;
            padding: 20px;
            margin: 10px;
            flex: 1 1 calc(33.333% - 40px);
            box-sizing: border-box;
            border-radius: 5px;
            min-width: 250px;
            max-width: calc(33.333% - 40px);
            text-align: left;
            display: flex;
            flex-direction: column;
        }

        .project-title {
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .project-description {
            font-size: 1em;
        }

        .project-image {
            width: 150px;
            height: auto;
            margin-bottom: 10px;
            margin-top: 10px;
            border-radius: 5px;
        }

        .live-demo {
            margin-top: auto; /* Ensures the live demo link is pushed to the bottom */
            font-size: 1.2em;
            color: white;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .live-demo:hover {
            text-decoration: underline;
        }

        .arrow {
            margin-left: 5px;
            font-size: 1.2em;
        }

        @media (max-width: 800px) {
            .project-box {
                flex: 1 1 calc(50% - 40px);
            }
        }

        @media (max-width: 500px) {
            .project-box {
                flex: 1 1 100%;
            }
        }
    </style>

</head>
<body>
    <a href="/">
        <header class="header">
            <div class="logo-text">lydr projects</div>
        </header>
    </a>
    <main class="main-content">
        <br><br>

        <p>All current and past projects</p>
        <div class="project-container">
            <div class="project-box">
                <div class="project-title"><a href="https://go.lydr.io" style="color:white;">go.lydr</a></div>
                <img class="project-image" src="go.png" alt="go.lydr">
                <div class="project-description">Simple Link shortener with possible custom link</div>
                <a class="live-demo" href="https://go.lydr.io">Live Demo <span class="arrow">➔</span></a>
            </div>
            <div class="project-box">
                <div class="project-title"><a href="https://dl.lydr.io" style="color:white;">dl.lydr</a></div>
                <img class="project-image" src="dl.png" alt="dl.lydr">
                <div class="project-description">File Host for all type of files</div>
                <a class="live-demo" href="https://dl.lydr.io">Live Demo <span class="arrow">➔</span></a>
            </div>
            <div class="project-box">
                <div class="project-title"><a href="/ar" style="color:white;">Edu AR</a></div>
                <div class="project-description">WebAR App for a Skeleton Model. TUM Seminar. Based on <a href="https://ar-js-org.github.io/AR.js-Docs/" style="color:white;">ar.js</a></div>
                <a class="live-demo" href="/ar">Live Demo <span class="arrow">➔</span></a>
            </div>
            <div class="project-box">
                <div class="project-title"><a href="https://tace.app" style="color:white;">Tace</a></div>
                <img class="project-image" src="tace.png" alt="Tace">
                <div class="project-description">Flutter based App. In Dev.</div>
                <a class="live-demo" href="https://tace.app">Live Demo <span class="arrow">➔</span></a>
            </div>
            <div class="project-box">
                <div class="project-title"><a href="https://github.com/tobikli/imgtoascii" style="color:white;">imgtoascii</a></div>
                <div class="project-description">Simple image to asciiart converter in python.</div>
                <a class="live-demo" href="https://github.com/tobikli/imgtoascii">Live Demo <span class="arrow">➔</span></a>
            </div>
            <div class="project-box">
                <div class="project-title"><a href="https://github.com/tobikli/lydr" style="color:white;">lydr</a></div>
                <img class="project-image" src="lydr.png" alt="lydr">
                <div class="project-description">This website.</div>
                <a class="live-demo" href="https://github.com/tobikli/lydr">Live Demo <span class="arrow">➔</span></a>
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
