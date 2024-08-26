<?php

session_start();

if (isset($_SESSION["login"]) && $_SESSION["login"] == 1) {
    header('Location: '."/profile");
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']); // Use htmlspecialchars to avoid XSS attacks
}

$red = '';
$sso = '';
if (isset($_GET['red'])) {
    $red = htmlspecialchars($_GET['red']); // Use htmlspecialchars to avoid XSS attacks
    $sso = "<a>SSO from ". $red . "</a>";
}



$html=<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | login</title>
    <link rel="icon" type="image/x-icon" href="/lydr_w.png">
    <link rel="stylesheet" href="/styles/login.css">
</head>
<body>
    <a href="/">
    <header class="header">
            <div class="logo-text">lydr login</div>
    </header>
    </a>
    <main class="main-content">
        <form method="POST" class="link-input" action="check.php">
<p>Login</p>
<p>$sso</p>
<br>
<input type="text" placeholder="User" name="loginname" required><br><br>
 <input name="loginpasswort" placeholder="Password" type=password required><br>
<br>
 <input type="hidden" name="red" value="$red">
<button type=submit name=submit >Login</button>
</form>

        <br> 
                <a href="/register" style="color: white; text-decoration: underline dashed">Register</a>

        <br>
        <a href="reset" style="color: white; text-decoration: underline dashed">Reset Password</a>
        <br>
        <span style="color:red;">$error</span>

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