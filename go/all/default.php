<?php



// Die URL der JSON-Datei
$json_url = 'https://go.lydr.io/redirects.json';

// Funktion zum Laden und Dekodieren der JSON-Datei
function loadJsonFromUrl($url) {
    $json_data = file_get_contents($url);
    if ($json_data === FALSE) {
        die('Fehler beim Laden der JSON-Datei');
    }

    $decoded_data = json_decode($json_data, true);
    if ($decoded_data === NULL) {
        die('Fehler beim Dekodieren der JSON-Daten');
    }

    return $decoded_data;
}

// Laden der JSON-Daten
$json_data = loadJsonFromUrl($json_url);

// Suchfunktion
$search_query = '';
$filtered_data = $json_data;

if (isset($_GET['search'])) {
    $search_query = strtolower($_GET['search']);
    $filtered_data = array_filter($json_data, function($url) use ($search_query) {
        return strpos(strtolower($url), $search_query) !== false;
    });
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>go.lydr | all links</title>
    <link rel="icon" type="image/x-icon" href="https://lydr.io/lydr.png">
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
            width: 100%;
            background: rgba(5, 5, 25, 0.7); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Apply Gaussian blur */
            -webkit-backdrop-filter: blur(10px); /* Safari support */
            z-index: 2; /* Ensure header is above main content */
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
            z-index: 1; /* Ensure main content is below header */
            overflow: hidden; /* Prevent scrolling on the main content */
        }
        
        .scrollable-content {
            width: 400px;
            max-width: 80%;
            max-height: 100vh - 500px; /* Set max height to avoid pushing the search bar under the header */
            margin-top: 20px; /* Margin to separate from search bar */
            background: rgba(19, 19, 41, 0.9); /* Slightly different background for scroll area */
            padding: 20px;
            box-sizing: border-box;
            overflow-y: auto; /* Make the content scrollable */
            overflow-x: auto; /* Make the content scrollable */


        }
        
        .search-content {
            width: 400px;
            max-width: 80%;
            max-height: 100vh - 500px; /* Set max height to avoid pushing the search bar under the header */
            margin-top: 30px; /* Margin to separate from search bar */
            padding-top: 10px;
            box-sizing: border-box;
        }
        
        footer {
            width: 100%;
            text-align: center;
            padding: 20px 0;
            position: fixed; /* Keep footer fixed at the bottom */
            bottom: 0;
            background: rgb(9, 9, 28); /* Add background to footer */
            z-index: 2; /* Ensure footer is above main content */
        }
        
        button {
            font-family: 'Courier New', Courier, monospace;
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

        .link-input {
            margin-top: 80px; /* Ensure the search bar is visible below the header */
            display: flex;
            align-items: center;
            flex-direction: column;
            width: 100%;
            max-width: 800px;
            position: relative; /* Ensure the position is relative */
            margin: 0 auto; /* Center the search bar */
            z-index: 1; /* Ensure search bar is below the header */
        }

        .link-input input[type="text"] {
            padding: 10px;
            font-size: 1em;
            border: 0px solid #ccc;
            border-radius: 0px;
            width: 100%;
            background: rgba(19, 19, 41, 0.9); /* Slightly different background for scroll area */
            color: white;
            font-family: 'Courier New', Courier, monospace;
            box-sizing: border-box;
        }

        .link-input button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: #001124;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            box-sizing: border-box;
        }

        .link-input button:hover {
            background-color: #001f43;
        }

        .shortened-url {
            margin-top: 10px;
            color: white;
            font-family: 'Courier New', Courier, monospace;
        }

        .shortened-url a {
            color: white;
            text-decoration: underline;
        }

        .shortened-url a:hover {
            text-decoration: none;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0; /* Remove default margin */
        }

        ul li {
            margin: 10px 0; /* Increased spacing between list items */
        }

        ul li a {
            color: white; /* Always white links */
        }

        ul li a:hover {
            text-decoration: underline;
        }

        @media (min-width: 800px) {
            .link-input {
                flex-direction: row;
            }

            .link-input button {
                width: auto;
                margin-top: 0;
                margin-left: 10px;
            }
        }
    </style>
</head>
<body>
        <script>
    document.addEventListener('DOMContentLoaded', function() {
        var currentPath = window.location.pathname;
        var redirectUrl = 'https://go.lydr.io/all';

        // Regular expression to match /go and any subpage under /go
        var regex = /^\/go(\/.*)?$/;

        if (regex.test(currentPath)) {
            console.log('Redirecting to:', redirectUrl); // Log the redirection for debugging
            window.location.href = redirectUrl;
        }
    });
</script>
    <a href="/">
    <header class="header">
        <img src="https://lydr.io/lydr.gif" alt="Image" class="logo">
            <div class="logo-text">go.lydr</div>
    </header>
    </a>
    <main class="main-content">
                    <div class="search-content">

        <form method="GET" class="link-input">
            <input type="text" name="search" placeholder="Search for a URL" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>

        <?php if ($search_query && empty($filtered_data)): ?>
            <p>No results found for "<?php echo htmlspecialchars($search_query); ?>"</p>
        <?php else: ?>
        </div>
            <div class="scrollable-content">
                
                <ul>
                    <?php foreach ($filtered_data as $short_link => $url): ?>
                        <li><a href="<?php echo htmlspecialchars($url); ?>"><?php echo htmlspecialchars($url); ?></a> - <span class="short-link"><?php echo htmlspecialchars($short_link); ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <br>
        <ul>
        <a href="/custom" style="color: white; text-decoration: underline dashed">Custom</a>&nbsp;
        <a href="/report" style="color: white; text-decoration: underline dashed">Report</a>
        </ul>
    </main>
    <footer>
        <ul>
            <li><a href="https://lydr.io">Home</a></li>
            <li><a href="/">Short</a></li>
            <li><a href="https://lydr.io/about">About</a></li>
        </ul>
    </footer>
</body>
</html>

