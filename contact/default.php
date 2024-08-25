<?php
session_start();

// Initialize the message variable
$mess = '';

// Check if there was a message stored in the session
if (isset($_SESSION['mess'])) {
    $mess = $_SESSION['mess'];
    unset($_SESSION['mess']); // Clear the message after displaying
}

$value = '';
$rdo = '';

if (isset($_SESSION['login'])) {
    $value = $_SESSION['email'];
    $rdo = 'readonly="readonly"';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["mail"]) && isset($_POST["msg"])) {
    $msg = trim($_POST["msg"]);
    $email = strtolower(trim($_POST["mail"]));

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $subject = "New Message on lydr";
        $message = "New Message from " . $email . "\n\n" . $msg;
        $headers = "From: contact@lydr.io";

        // Send the email
        if (mail("tobikli@pm.me", $subject, $message, $headers)) {
            $_SESSION['mess'] = 'Message sent!';
        } else {
            $_SESSION['mess'] = 'Could not send Message!';
        }

        // Redirect to avoid resending the email
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        $_SESSION['mess'] = 'Invalid Email!';
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | contact</title>
    <link rel="icon" type="image/x-icon" href="/lydr_w.png">
    <link rel="stylesheet" href="/styles/login.css">
</head>
<body>
    <a href="/">
    <header class="header">
            <div class="logo-text">lydr contact</div>
    </header>
    </a>
    <main class="main-content">
        <form method="POST" class="link-input" action="">
            <b>Contact</b><br>
            <br>
            <input type="text" placeholder="Email" name="mail" value="$value" $rdo required><br><br>
             <textarea name="msg" placeholder="Message" required></textarea><br>
            <br>
            <button type="submit" name="submit">Send</button>
            </form>
        <br>
        <span style="color:white;">$mess</span>
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
