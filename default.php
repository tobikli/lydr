<?php
session_start();

// Determine profile or login link based on session
$profile = '<div class="grid-item long">
                <div class="project-title">Login</div>
                <div class="project-description">Access your account and manage your preferences.</div>
                <a href="/login"><button>Login</button></a>
            </div>';

if (isset($_SESSION["login"]) && $_SESSION["login"] == 1) {
    $username = $_SESSION["username"]; // Assuming username is stored in session
    $profile = '<div class="grid-item long">
                <div class="project-title">Welcome, '.htmlspecialchars($username).'</div>
                <div class="project-description">Manage your preferences.</div>
                    <a href="/profile"><button class="btn-profile">Profile</button></a>
                    <a href="/logout?red=/"><button style="margin-top:10px;">Logout</button></a>
                </div>';
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr</title>
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

        .username-display {
            color: white;
            font-size: 1em;
            margin-top: 20px;
        }

        .main-content {
            flex: 1; /* Allows content to fill remaining space */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 80px 20px 40px; /* Padding for header and footer */
            color: white;
            font-size: 1em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            width: 100%;
            box-sizing: border-box; /* Include padding in element's width and height */
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 1200px;
            margin-top: 20px;
            padding: 0 10px; /* Padding to prevent grid items from touching the screen edges */
            box-sizing: border-box;
        }

        .grid-item {
            background-color: #131329;
            color: white;
            padding: 20px;
            border: 1px solid #001124;
            border-radius: 5px;
            box-sizing: border-box;
            text-align: left;
        }
        
        .grid-item .button-container {
    display: flex;
    gap: 10px; /* Space between buttons */
    margin-top: 10px; /* Space above buttons */
}

        .grid-item button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: #001124;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: auto; /* Adjust width to fit button content */
            box-sizing: border-box;
            transition: background-color 0.3s;
        }
        
        .btn-settings {
            margin-top: 0px;
        }
        
    
        
        .btn-settings:hover {
            background-color: #45a049;
        }

        .grid-item.large {
            grid-column: span 2;
        }

        .grid-item.wide {
            grid-column: span 3;
        }

        .grid-item.small {
            grid-column: span 1;
            height: 150px;
        }

        .grid-item.long {
            grid-column: span 1;
            grid-row: span 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .grid-item.long1 {
            grid-column: span 1;
            grid-row: span 3;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .grid-item .project-title {
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .grid-item .project-description {
            font-size: 1em;
        }

        .grid-item button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: #001124;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .grid-item btn-settings {
            margin-top: 10px;
        }

        .grid-item button:hover {
            background-color: #001f43;
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
            padding: 2px; /* Remove padding */
 
        }
        
        h1, h2, h3, h4, h5, h6, h7
        {
            font-weight: normal;
        }

        footer ul li a:hover {
            text-decoration: underline;
        }

        /* Mobile view adjustments */
        @media (max-width: 800px) {
            .grid-container {
                grid-template-columns: 1fr; /* Single column on smaller screens */
            }

            .grid-item.large,
            .grid-item.wide,
            .grid-item.long {
                grid-column: span 1; /* All items take up one column */
                grid-row: auto; /* Remove row spanning */
            }
        }
        
        .socialmediaicons .fa:hover {
              color:white;
              opacity:0.8;
            }
            .socialmediaicons .fa {
              padding: 10px;
              font-size: 20px;
              width: 25px;
              text-align: center;
              text-decoration: none;
              margin: 5px 2px;
            }
            
        
        .socialmediaicons .fa-github {
            background: #1d2127;
            color: white;
        }
        .socialmediaicons .fa-linkedin {
            background: #007bb5;
            color: white;
        }
        
        .socialmediaicons .fa-instagram {
            background: #125688;
            color: white;
        }
        
        .socialmediaicons .fa-reddit {
            background: #ff5700;
            color: white;
        }
    </style>

</head>
<body>
    <header class="header">
        <img src="https://lydr.io/lydr.gif" alt="Image" class="logo">
        <div class="logo-text">lydr</div>
    </header>

    <main class="main-content">
        
        <div class="grid-container">
            <div class="grid-item large">
                <div class="project-title">Hello,</div>
                <div class="project-description">I am Tobias, a Computer Science student at <a href="https://tum.de">TUM</a>. I’m passionate about developing apps and websites, and I love exploring new technologies, science, and art. Check out my portfolio to see how I blend creativity with coding.</div>

            </div>
            
            $profile
            
            <div class="grid-item long1">
                <div class="project-title">Quick Links</div>
                <div class="project-description">
                <ul>
                    <li>GitHub: <a href="https://github.com/tobikli">github/tobikli</a></li>
                    <li>Dl: <a href="https://dl.lydr.io">dl.lydr.io</a></li>
                    <li>Go: <a href="https://go.lydr.io">go.lydr.io</a></li>
                    <li>AR: <a href="/ar">lydr.io/ar</a></li>
                    <li>Tace: <a href="https://tace.app">tace.app</a></li>
                    <li>TUM: <a href="https://home.in.tum.de/~klin/#">in.tum</a></li>
                </ul>
                <br>
                <div class="socialmediaicons">
                    <a href="https://github.com/tobikli" class="fa fa-github"></a>
                    <a href="https://www.linkedin.com/in/tobias-klingenberg/" class="fa fa-linkedin"></a>
                    <a href="https://www.instagram.com/tob1wen/" class="fa fa-instagram"></a>
                    <a href="https://www.reddit.com/user/tobiji/" class="fa fa-reddit"></a>
                </div>
                <br>
                <a href="/admin">Admin Panel</a>
                </div>
            </div>
            
            
            <div class="grid-item wide">
                <div class="project-title">Projects</div>
                <div class="project-description">Explore all my latest work and ongoing projects.</div>
                <a href="/projects"><button>View Projects</button></a>
            </div>

            <div class="grid-item wide">
                <div class="project-title">Curriculum Vitae</div>
                <div class="project-description">View my professional background, skills, and experience.</div>
                <a href="/cv"><button>View CV</button></a>
            </div>

            

        </div>
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
