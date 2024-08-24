<?php
// Function to generate a random path of specified length
function generateRandomPath($length = 6) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $path = '';
    for ($i = 0; $i < $length; $i++) {
        $path .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $path;
}

// Function to load redirects from file
function loadRedirectsFromFile($filename) {
    if (file_exists($filename)) {
        $content = file_get_contents($filename);
        if ($content !== false) {
            return json_decode($content, true);
        }
    }
    return [];
}

// Function to save redirects to file
function saveRedirectsToFile($filename, $redirects) {
    $json = json_encode($redirects, JSON_PRETTY_PRINT);
    file_put_contents($filename, $json);
}

function isSecondLevelDomainLydrIo($url) {
    // Parse the URL to get the host
    $parsedUrl = parse_url($url, PHP_URL_HOST);
    
    // If the URL is not valid, return false
    if ($parsedUrl === false) {
        return false;
    }

    // Get the host parts
    $hostParts = explode('.', $parsedUrl);

    // Check if the host ends with 'lydr.io'
    $hostCount = count($hostParts);
    if ($hostCount >= 2) {
        $secondLevelDomain = $hostParts[$hostCount - 2] . '.' . $hostParts[$hostCount - 1];
        return $secondLevelDomain === 'lydr.io';
    }

    return false;
}

// Determine the protocol (http or https)
$protocol = 'http://';
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
    $protocol = 'https://';
}

// Get the host name
$host = $_SERVER['HTTP_HOST'];

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'];

// Combine them to get the full URL
$fullUrl = $protocol . $host . $requestUri;

// Trim any whitespace
$fullUrl = trim($fullUrl);

// Check if $fullUrl contains "/go/"
if (strpos($fullUrl, "go/") !== false) {
    // Ensure no output before the header
    ob_start();
    header("Location: https://go.lydr.io");
    exit;
}


$redirectsFile = 'redirects.json'; // File to store redirects

// Load existing redirects from file
$redirects = loadRedirectsFromFile($redirectsFile);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    // Sanitize the URL input
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);

    $url = strtolower($url);

    // Check if URL doesn't contain http or https, prepend http://
    if (!preg_match("/^(http|https):/", $url)) {
        $url = "http://" . $url;
    }

    // Validate the URL using filter_var
    $pattern = '/\b((http(s)?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,})(\/[^\s]*)?\b/';

    if (!preg_match($pattern, $url)) {
        $shortenedUrlContent = '<p style="color: red;">Invalid URL. Please enter a valid website address.</p>';
    } else {
        if (isSecondLevelDomainLydrIo($url)) {
            $shortenedUrlContent = '<p style="color: white;">Can\'t short lydr.io websites.</p>';
        } else {
        // Check if the URL already exists in the redirects array
        $existingPath = array_search($url, $redirects);
        if ($existingPath !== false) {
            // If URL exists, use the existing path
            $shortenedUrl = "https://go.lydr.io$existingPath";
            $shortenedShow = "go.lydr.io$existingPath";
        } else {
            // Generate a random path
            $path = '/' . generateRandomPath();

            // Ensure the generated path is unique
            while (isset($redirects[$path])) {
                $path = '/' . generateRandomPath();
            }

            // Add the new redirection to the $redirects array
            $redirects[$path] = $url;

            // Save the updated $redirects array to file
            saveRedirectsToFile($redirectsFile, $redirects);

            // Construct the shortened URL
            $shortenedUrl = "https://go.lydr.io$path";
            $shortenedShow = "go.lydr.io$path";
            
        }

        $shortenedUrlContent = "<div style=\"border: 0px solid #ccc; padding: 10px; width: 300px; text-align: center; background: #131329\">
            <div class=\"shortened-url\">
                <a href=\"$shortenedUrl\" target=\"_blank\" style=\"color: rgb(255, 255, 255); text-decoration: underline dashed; text-decoration-thickness: 0.5px;\">$shortenedShow</a>
            </div>
            <div style=\"margin-top: 10px;\">
                       <button class=\"styled-button\" onclick=\"copyUrl()\">Copy</button>
                        <button class=\"styled-button\" onclick=\"shareUrl()\">Share</button>
            </div>
                <div id=\"message\" style=\"margin-top: 10px; color: white;\"></div>
        
                </div>
                
        <style>
            .styled-button {
                background-color: #001124;
                color: white;
                border: none;
                padding: 10px 20px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 14px;
                margin: 5px 2px;
                cursor: pointer;
                border-radius: 4px;
                transition: background-color 0.3s ease;
            }
            
            .styled-button:hover {
                background-color: #001f43;
            }
        </style>
        <script>
            function copyUrl() {
                const url = \"$shortenedUrl\";
                navigator.clipboard.writeText(url).then(() => {
                    document.getElementById('message').innerText = 'URL copied to clipboard';
                }).catch(err => {
                    document.getElementById('message').innerText = 'Failed to copy URL';
                    document.getElementById('message').style.color = 'red';
                });
            }
        
            function shareUrl() {
                const url = \"$shortenedUrl\";
                if (navigator.share) {
                    navigator.share({
                        title: 'Check out this link',
                        url: url
                    }).catch(err => {
                        //alert('Failed to share URL');
                    });
                } else {
                    alert('Share not supported on this browser');
                }
            }
        </script>
        ";
    }
        
    }
} else {
    $shortenedUrlContent = ''; // Set to empty if no submission
}

// Check if this path exists in redirects and redirect if found
$thisPath = $_SERVER['REQUEST_URI'];
if (isset($redirects[$thisPath])) {
    // Redirect to the specified URL
    header("Location: " . $redirects[$thisPath]);
    exit;
}


// Default HTML content
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>go.lydr | short links</title>
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
            position: absolute;
            background: rgb(5,5,25); /* Add background to header */
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
            justify-content: center;
            align-items: center;
            flex-direction: column; /* Added to stack items vertically */
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

        .link-input {
            margin-top: 20px;
            display: flex;
            align-items: center;
            flex-direction: column;
            width: 100%;
            max-width: 800px; /* Increased default width */
        }

        .link-input input[type="text"] {
            padding: 10px;
            font-size: 1em;
            border: 0px solid #ccc;
            border-radius: 0px;
            width: 100%;
            background-color: #131329; /* Dark background */
            color: white; /* White text */
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
        input {
            display: block;
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
    <a href="/">
    <header class="header">
        <img src="https://lydr.io/lydr.gif" alt="Image" class="logo">
            <div class="logo-text">go.lydr</div>
    </header>
    </a>
    <main class="main-content">
        <p>Short a link</p>
        <form class="link-input" action="" method="post">
            <input type="text" name="url" placeholder="Enter URL" required>
            <button type="submit">Shorten</button>
        </form>
        <br> 
        $shortenedUrlContent
    </main>
    <footer>
        <ul>
            <li><a href="https://lydr.io">Home</a></li>
            <li><a href="/all">All</a></li>
            <li><a href="/custom">Custom</a></li>
            <li><a href="/report">Report</a></li>
            <li><a href="https://lydr.io/about">About</a></li>

        </ul>
    </footer>
</body>
</html>
HTML;

// Output the HTML content
echo $html;
?>
