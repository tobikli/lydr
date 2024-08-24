<?php

$html=<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | files</title>
    <link rel="icon" type="image/x-icon" href="/lydr.png">
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
            background: rgb(5,5,25); /* Add background to header */
            position: absolute;
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
            flex-direction: column; /* Change to column layout */
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

        #moreLinks {
            margin-top: 10px;
        }

    </style>
</head>
<body>
    <a href="/">
    <header class="header">
        <img src="https://lydr.io/lydr.gif" alt="Image" class="logo">
            <div class="logo-text">lydr files</div>
    </header>
    </a>
    <main class="main-content">
        <p>Empty.</p>
    </main>
    <footer>
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/admin">Login</a></li>
            <li><button id="moreLinksButton">></button></li>
        </ul>
        <div id="moreLinks" class="hidden">
            <ul>
                <li><a href="/api">API</a></li>
                <li><a href="/ar">AR</a></li>
                <li><a href="https://go.lydr.io">GO</a></li>
                <li><a href="https://dl.lydr.io">DL</a></li>
                <li><a href="/s">iOS</a></li>

            </ul>
        </div>
    </footer>
        <script>
        document.getElementById('moreLinksButton').addEventListener('click', function() {
            var moreLinks = document.getElementById('moreLinks');
            if (moreLinks.classList.contains('hidden')) {
                moreLinks.classList.remove('hidden');
                this.textContent = '<';
            } else {
                moreLinks.classList.add('hidden');
                this.textContent = '>';
            }
        });

    </script>
</body>
</html>
HTML;

echo $html;


?>