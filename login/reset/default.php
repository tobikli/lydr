<?php

session_start();

$error = '';
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']); // Use htmlspecialchars to avoid XSS attacks
}


$msg = '';
if (isset($_GET['message'])) {
    $msg = htmlspecialchars($_GET['message']); // Use htmlspecialchars to avoid XSS attacks
}


$html=<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | reset</title>
    <link rel="icon" type="image/x-icon" href="/lydr_w.png">
    <link rel="stylesheet" href="/styles/login.css">

</head>
<body>
    <a href="/">
    <header class="header">
            <div class="logo-text">lydr reset</div>
    </header>
    </a>
    <main class="main-content">
        <form method="POST" class="link-input" action="check.php">
<p>Reset</p>
<br>
<input type="text" placeholder="Email" name="email" required><br><br>
<button type=submit name=submit >Submit</button>
</form>

        <br> 
                <a href="/login" style="color: white; text-decoration: underline dashed">Login</a>

      
        <br>
        <span style="color:red;">$error</span>
        <span style="color:white;">$msg</span>

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