<?php
session_start();

require_once 'minify.php';

// Determine profile or login link based on session
$profile = '<div class="grid-item long">
                <div class="project-title"><c>Login</c></div>
                <div class="project-description1"><c>Access your account and manage your preferences.</c></div>
                <a href="/login"><button>Login</button></a>
            </div>';

$username = '';

if (isset($_SESSION["login"]) && $_SESSION["login"] == 1) {
    $username = $_SESSION["username"]; // Assuming username is stored in session
    $profile = '<div class="grid-item long">
                <div class="project-title"><c>Welcome, '.htmlspecialchars($username).'</c></div>
                <div class="project-description1"><c>Manage your preferences.</c></div>
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
    <meta name="description" content="Portfolio and Project Website of Tobias Klingenberg. CS Student at TUM with Passion for development and science.">
    <title>lydr</title>
    <link rel="icon" type="image/x-icon" href="/lydr_w.png">
    <link rel="shortcut icon" href="/lydr_w.png" />
    <link rel="apple-touch-icon" href="/lydr_w.png" /><
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/styles/home.css">
    <link rel="stylesheet" href="/styles/cursor.css">
    <script src="/scripts/cursor.js"></script>
    <script type="module" src="/scripts/three.js"></script>
</head>
<body>
    <header class="header">
        <div class="header-content">
        <img src="/lydr_a.gif" alt="Image" class="logo">
        <div class="logo-text">lydr</div>
    <!--</div>
        <div class="header-right">
        <a href="/profile">$username</a>
    </div>-->
    </header>
    <main class="main-content">
        <div class="grid-container">
            <div class="grid-item large">
                <div class="project-title"><c>Hello,</c></div>
                <div class="project-description"><c>I am Tobias, a Computer Science student at <a style="text-decoration:underline;" href="https://tum.de">TUM</a>. I’m passionate about developing apps and websites, and I love exploring new technologies, science, and art. Check out my portfolio to see how I blend creativity with coding.</c></div>
            </div>
            <div class="grid-item long">
                <div class="project-title"><c>Contact</c></div>
                <div class="project-description1"><c>Send me a message.</c></div>
                <a href="/contact"><button>Contact</button></a>
            </div>
            <div class="grid-item long1">
                <div class="project-title"><c>Quick Links</c></div>
                <div class="project-description">
                <ul>
                    <li>GitHub: <a href="https://github.com/tobikli">github/tobikli</a></li>
                    <li>Dl: <a href="https://dl.lydr.io">dl.lydr.io</a></li>
                    <li>Go: <a href="https://go.lydr.io">go.lydr.io</a></li>
                    <li>AR: <a href="/ar">lydr.io/ar</a></li>
                    <li>Tace: <a href="https://tace.app">tace.app</a></li>
                    <li>TUM: <a href="https://home.in.tum.de/~klin/#">in.tum</a></li>
                    <li>My Room: <a href="https://rooms.xyz/tobiwen/mucroom">rooms/tobiwen</a></li>
                </ul>
                <br>
                </div>
                <div class="socialmediaicons">
                    <a href="https://github.com/tobikli" class="fa fa-github"></a>
                    <a href="https://www.linkedin.com/in/tobias-klingenberg/" class="fa fa-linkedin"></a>
                    <a href="https://www.instagram.com/tob1wen/" class="fa fa-instagram"></a>
                    <a href="https://www.reddit.com/user/tobiji/" class="fa fa-reddit"></a>
                </div>
                <div class="project-description">
                <br>
                <ul>
                <li><a href="/files">Files</a></li>
                <li><a href="/admin">Admin Panel</a></li>
                </ul>
                </div>
            </div>
            
            <div class="grid-item wide">
                <div class="project-title"><c>Projects</c></div>
                <div class="project-description1"><c>Explore all my latest work and ongoing projects.</c></div>
                <a href="/projects"><button>View Projects</button></a>
            </div>

            <div class="grid-item long">
                <div class="project-title"><c>CV</c></div>
                <div class="project-description1"><c>View my Curriculum Vitae.</c></div>
                <a href="/cv"><button>View CV</button></a>
            </div>
            <div class="grid-item med">
                <div class="project-title"><c>Publications</c></div>
                <div class="project-description1"><c>View my publications.</c></div>
                <a href="/pub"><button>View Publications</button></a>
            </div>
            $profile
            
        </div>
        <!--<a href="https://rooms.xyz/tobiwen"><img src="/images/rooms.gif" alt="Rooms Account" class="bottom-right-image"></a>-->
    </main>

    <footer>
        <ul>
            <li><a href="https://lydr.io/about">Made with ♡ by Tobias</a></li>
        </ul>
    </footer>
</body>
</html>
HTML;

echo ($html);
?>
