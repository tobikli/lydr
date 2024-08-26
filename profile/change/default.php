<?php
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["login"] != 1) {
    header('Location: /login?red=/profile/change');
    exit;
}

$mess = '';

// Check if there was a message stored in the session
if (isset($_SESSION['mess1'])) {
    $mess = $_SESSION['mess1'];
    unset($_SESSION['mess1']); // Clear the message after displaying
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["mail"]) && isset($_POST["mail1"])) {
    $mail = strtolower(trim($_POST["mail"]));
    $mail1 = strtolower(trim($_POST["mail1"]));

    if (filter_var($mail, FILTER_VALIDATE_EMAIL) && filter_var($mail1, FILTER_VALIDATE_EMAIL) && strcmp($mail, $mail1) === 0) {
        
        $usersFile = __DIR__ . '/../../users.json';

        if (file_exists($usersFile)) {
            $usersData = json_decode(file_get_contents($usersFile), true);
        } else {
            die("Users file not found.");
        }
        
        // Check if the new email is already taken
        foreach ($usersData as $user) {
            if ($user["email"] === $mail && $user["email"] !== $_SESSION['email']) {
                $_SESSION['mess1'] = 'Email already taken!';
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        }

        // Update the user's email
        $userFound = false;
        foreach ($usersData as &$user) {
            if ($user['email'] === $_SESSION['email']) {
                $user['verified'] = 0;
                $user['email'] = $mail;
                $userFound = true;
                break;
            }
        }
        
        if ($userFound) {
            file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));
            
            // Update session variables after successful email change
            $_SESSION["email"] = $mail;
            $_SESSION["verified"] = 0;
            
            // Redirect to profile page
            header("Location: /profile");
            exit();
        } else {
            $_SESSION['mess1'] = 'User not found!';
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
    } else {
        $_SESSION['mess1'] = 'Invalid Email or Emails do not match!';
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
    <title>lydr | profile</title>
    <link rel="icon" type="image/x-icon" href="/lydr_w.png">
    <link rel="stylesheet" href="/styles/login.css">
</head>
<body>
    <a href="/">
    <header class="header">
            <div class="logo-text">lydr profile</div>
    </header>
    </a>
    <main class="main-content">
        <form method="POST" class="link-input" action="">
            <p>Change Email</p><br>
            <br>
            <input type="text" placeholder="Email" name="mail" required><br><br>
            <input type="text" placeholder="Repeat Email" name="mail1" required><br><br>
            <br>
            <button type="submit" name="submit">Change</button>
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
