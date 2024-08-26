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
            <div class="logo-text">lydr register</div>
    </header>
    </a>
    <main class="main-content">
        <form method="POST" class="link-input" action="check.php">
<p>Register</p>
<br>
<input type="text" placeholder="User" name="username" required><br><br>
<input type="text" placeholder="Mail" name="email" required><br><br>
 <input name="password" placeholder="Password" type=password required><br>
<br>
<button type=submit name=submit >Register</button>
</form>

        <br> 
                <span style="color:red;">$error</span>

    </main>
    <footer>
        <ul>
            <li><a href="https://lydr.io">Home</a></li>
           <li><a href="/login">Login</a></li>
           
            <li><a href="https://lydr.io/about">About</a></li>

        </ul>
    </footer>
</body>
</html>
HTML;

echo $html;
?>