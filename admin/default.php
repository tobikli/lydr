<?php

session_start();

if (!isset($_SESSION["login"]) || $_SESSION["login"] != 1) {
    header('Location: '."/login?red=/admin");
    exit;
}

$userid = $_SESSION["userid"];
$status = $_SESSION["status"];

if($status == "admin"){
    // Benutzerliste aus der JSON-Datei im übergeordneten Verzeichnis abrufen
    $jsonData = file_get_contents(__DIR__ . '/../users.json');
    $users = json_decode($jsonData, true); // JSON-Daten als assoziatives Array dekodieren
    
    $jsonData = file_get_contents(__DIR__ . '/../go/custom/requests.json');
    $cus = json_decode($jsonData, true); // JSON-Daten als assoziatives Array dekodieren
    
    $jsonData = file_get_contents(__DIR__ . '/../go/report/requests.json');
    $gorep = json_decode($jsonData, true); // JSON-Daten als assoziatives Array dekodieren
    
    $jsonData = file_get_contents(__DIR__ . '/../dl/report/requests.json');
    $dlrep = json_decode($jsonData, true); // JSON-Daten als assoziatives Array dekodieren
    

    
    

    // Suchbegriff verarbeiten
    $searchQuery = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

    $filteredUsers = array_filter($users, function($user) use ($searchQuery) {
        // Suche nach Username, Email, Status oder ID
        return empty($searchQuery) ||
               strpos(strtolower($user['username']), $searchQuery) !== false ||
               strpos(strtolower($user['email']), $searchQuery) !== false ||
               strpos(strtolower($user['status']), $searchQuery) !== false ||
               strpos(strval($user['userid']), $searchQuery) !== false;
    });


    
    $userHtml = '<div class="user-list"><ul>';
    foreach ($filteredUsers as $user) {
                $userid = $user['userid'];

        // Anzeige der Benutzerdaten ohne das Passwort
        
        // Path to the name.txt file
$nameFile = __DIR__ . '/../profile/l/' . $userid . '/name.txt';
        $filename = '';

// Check if the file exists and read its contents
if (file_exists($nameFile)) {
    $lines = file($nameFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Ensure there are exactly 3 lines
    if (count($lines) >= 0) {
        $filename = trim($lines[0]);
    }
}

// Ensure values are safe to use in HTML
$filename = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
        
        $profilePictureUrl = "/profile/l/$userid/$filename"; // Placeholder profile picture URL
        
        $userHtml .= '<li>';
        $userHtml .= '<img src="'.$profilePictureUrl.'" style="height:50px; width:50px; object-fit: cover; border-radius: 50%; border: 1px solid #000000;" alt="Profile Image">';
        $userHtml .= 'ID: ' . htmlspecialchars($user['userid']) . '<br>';
        $userHtml .= 'Username: ' . htmlspecialchars($user['username']) . '<br>';
        $userHtml .= 'Email: <a>' . htmlspecialchars($user['email']) . '</a><br>';
        $userHtml .= 'Verified: ' . ($user['verified'] ? 'Yes' : 'No') . '<br>';
        $userHtml .= 'Status: ' . htmlspecialchars($user['status']);
        $userHtml .= '</li><br>';
    }
    $userHtml .= '</ul></div>';
    
    $custom = '<div class="user-list"><ul>';
    
    if(!$cus){
        $custom .= "Empty";
    }

    foreach ($cus as $k => $v) {
        // Anzeige der Benutzerdaten ohne das Passwort
        $custom .= '<li>';
        $custom .= 'Custom: ' . htmlspecialchars($k) . '<br>';
        $custom .= 'Link: <a>' . htmlspecialchars($v) . '</a><br>';
        $custom .= '</li><br>';
    }

    $custom .= '</ul></div>';
    
    $goreports = '<div class="user-list"><ul>';
    
    if(!$gorep){
        $goreports .= "Empty";
    }

    foreach ($gorep as $k => $v) {
        // Anzeige der Benutzerdaten ohne das Passwort
        $goreports .= '<li>';
        $goreports .= 'Reason: ' . htmlspecialchars($k) . '<br>';
        $goreports .= 'Link: <a>' . htmlspecialchars($v) . '</a><br>';
        $goreports .= '</li><br>';
    }

    $goreports .= '</ul></div>';
    
    $dlreports = '<div class="user-list"><ul>';
    
    if(!$dlrep){
        $dlreports .= "Empty";
    }

    foreach ($dlrep as $k => $v) {
        // Anzeige der Benutzerdaten ohne das Passwort
        $dlreports .= '<li>';
        $dlreports .= 'Reason: ' . htmlspecialchars($k) . '<br>';
        $dlreports .= 'Link: <a>' . htmlspecialchars($v) . '</a><br>';
        $dlreports .= '</li><br>';
    }

    $dlreports .= '</ul></div>';
    
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | admin</title>
    <link rel="icon" type="image/x-icon" href="https://lydr.io/lydr.png">
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
            position: fixed; /* Keep header fixed at the top */
            top: 0;
            left: 0;
            background: rgb(5,5,25); /* Add background to header */
            z-index: 2; /* Ensure header is above main content */
        }
        
        .logo {
            width: 100px; /* Adjust size as needed */
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
        
        .user-list {
            max-height: 500px; /* Set the height of the scrollable container */
            overflow-y: scroll; /* Enable vertical scrolling */
            width: 100%;
            background-color: rgba(255, 255, 255, 0.1); /* Slightly transparent background for the list */
            padding: 20px;
            border-radius: 10px;
            box-sizing: border-box;
            margin: 10px;
        }

        .user-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .user-list ul li {
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            margin-bottom: 10px;
            border-radius: 5px;
            text-align: left;
        }

        footer {
            width: 100%;
            text-align: center;
            padding: 20px 0;
            background-color: rgb(5,5,25); /* Match background to header */
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

        .search-form {
            margin-bottom: 20px;
        }

        .search-input {
            padding: 10px;
            font-size: 1em;
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .search-button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: #001124;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-button:hover {
            background-color: #001f43;
        }
    </style>
</head>
<body>
    <a href="/">
    <header class="header">
            <img src="https://lydr.io/lydr.png" alt="Image" class="logo">
            <div class="logo-text">lydr admin</div>
    </header>
    </a>
    <main class="main-content">
    <br>
        <h2>Users</h2>
        <form method="GET" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Search users..." value="$searchQuery">
            <button type="submit" class="search-button">Search</button>
        </form>
        $userHtml
        <h2>Requests</h2>
        <h3>GO</h3>
        Custom Links <br>
        $custom
        Reports <br>
        $goreports
        <h3>DL</h3>
        Reports <br>
        $dlreports
    </main>
<footer>
    <ul>
        <li><a href="https://lydr.io">Home</a></li>
        <li><a href="/logout">Logout</a></li>
        <li><a href="https://lydr.io/about">About</a></li>
    </ul>
</footer>
</body>
</html>
HTML;

    echo $html;
} else {
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lydr | admin</title>
    <link rel="icon" type="image/x-icon" href="https://lydr.io/lydr.png">
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
            position: fixed; /* Keep header fixed at the top */
            top: 0;
            left: 0;
            background: rgb(5,5,25); /* Add background to header */
            z-index: 2; /* Ensure header is above main content */
        }
        
        .logo {
            width: 100px; /* Adjust size as needed */
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
            background-color: rgb(5,5,25); /* Match background to header */
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
    </style>
</head>
<body>
    <a href="/">
    <header class="header">
            <img src="https://lydr.io/lydr.png" alt="Image" class="logo">
            <div class="logo-text">lydr admin</div>
    </header>
    </a>
    <main class="main-content">
        <h3>Insufficient Privileges</h3>
    </main>
<footer>
    <ul>
        <li><a href="https://lydr.io">Home</a></li>
        <li><a href="/logout">Logout</a></li>
        <li><a href="https://lydr.io/about">About</a></li>
    </ul>
</footer>
</body>
</html>
HTML;

    echo $html;
}
?>