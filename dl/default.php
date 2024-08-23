<?php



// Function to generate a random path of specified length
function generateRandomPath($length = 8) {
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

// Define constants
define('MAX_FILE_SIZE', 1 * 1024 * 1024 * 1024); // 1 GB
$redirectsFile = 'links.json'; // File to store redirects

// Load existing redirects from file
$redirects = loadRedirectsFromFile($redirectsFile);


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

// Check if $fullUrl contains "/dl/"
if (strpos($fullUrl, "dl/") !== false) {
    // Ensure no output before the header
    ob_start();
    header("Location: https://dl.lydr.io");
    exit;
}

$name = "";

$shortenedUrlContent = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $originalFilename = $file['name'];
    $tempPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $name = $originalFilename;
    $hash = hash_file('sha256', $tempPath);
    // Check file size
    if ($fileSize > MAX_FILE_SIZE) {
        $shortenedUrlContent = "<div style=\"color: red;\">File size exceeds 1 GB limit.</div>";
    } else {
        // Ensure the path is unique
        do {
            $path = '/d/' . generateRandomPath();
        } while (isset($redirects[$path]));

        // Create the directory for the new path
        $directoryPath = __DIR__ . $path;
        mkdir($directoryPath, 0777, true);

        // Move the uploaded file to the new directory
        $destinationPath = $directoryPath . '/' . basename($originalFilename);
        if (move_uploaded_file($tempPath, $destinationPath)) {
            // Save file details in name.txt
            date_default_timezone_set('UTC+2');
            $uploadDate = date('Y-m-d H:i:s');
            $fileDetails = "$originalFilename\n$uploadDate\n" . number_format($fileSize / 1024 / 1024, 2) . " MB\n" . "$hash\n$path";
            file_put_contents($directoryPath . '/name.txt', $fileDetails);
            chmod($directoryPath . '/name.txt', 0640);

            
            $exampleFilePath = __DIR__ . '/example.txt';
            $defaultFilePath = $directoryPath . '/default.php';

            // Check if example.php exists and copy it to default.php
            if (file_exists($exampleFilePath)) {
                if (!copy($exampleFilePath, $defaultFilePath)) {
                    die('Failed to copy example.php to default.php');
                }
            } else {
                die('example.php not found');
            }

            // Add the new path to the redirects
            $redirects[$path] = $originalFilename;
            saveRedirectsToFile($redirectsFile, $redirects);

            // Construct the shortened URL
            $shortenedUrl = "https://dl.lydr.io$path";
            $shortenedShow = "dl.lydr.io$path";

            $shortenedUrlContent = "<div style=\"border: 0px solid #ccc; padding: 10px; width: 300px; text-align: center; background: #131329\">
                <div class=\"shortened-url\">
                    <a href=\"$shortenedUrl\" target=\"_blank\" style=\"color: rgb(255, 255, 255); text-decoration: underline dashed; text-decoration-thickness: 0.5px;\">$shortenedShow</a>
                </div>
                <div style=\"margin-top: 10px;\">
                       <button class=\"styled-button\" onclick=\"copyUrl('$shortenedUrl')\">Copy</button>
                        <button class=\"styled-button\" onclick=\"shareUrl('$shortenedUrl', '$originalFilename')\">Share</button>
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
            </style>";
            
            // Delete the temporary file after processing
            //unlink($tempPath);
        } else {
            $shortenedUrlContent = "<div style=\"color: red;\">Failed to upload file.</div>";
        }
    }
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dl.lydr | Upload</title>
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
        
        .file-input {
            margin-top: 20px;
            display: flex;
            align-items: center;
            flex-direction: column;
            width: 100%;
            max-width: 800px; /* Increased default width */
            border: 2px dashed #001124;
            padding: 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
        }

        .file-input.dragover {
            background-color: #001124;
        }

        .file-input input[type="file"] {
            display: none;
        }

        .file-input button {
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

        .file-input button:hover {
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

        .progress-container {
            width: 100%;
            max-width: 800px;
            background-color: #ddd;
            border-radius: 4px;
            margin-top: 10px;
            display: none; /* Hidden by default */
        }

        .progress-bar {
            height: 20px;
            width: 0;
            background-color: #4caf50;
            border-radius: 4px;
            text-align: center;
            line-height: 20px;
            color: white;
        }
    </style>
</head>
<body>
    <a href="/">
    <header class="header">
            <img src="https://lydr.io/lydr.png" alt="Image" class="logo">
            <div class="logo-text">dl.lydr</div>
    </header>
    </a>
    <main class="main-content">
        <p>Upload a file</p>
        <form action="" method="post" enctype="multipart/form-data" class="file-input" id="uploadForm">
            <input type="file" id="fileInput" name="file" required>
            <p>Drag & Drop your file here or</p>
            <button type="button" id="customButton">Choose file 
            <img src="/upload.png" height="18px" alt="buttonpng" border="0" />
            </button>
            <button type="submit" style="font-style:normal;">Upload</button>
            <div id="progressContainer" class="progress-container">
                <div id="progressBar" class="progress-bar">0%</div>
            </div>
        </form>
        <br> 
        $shortenedUrlContent
    </main>
    <footer>
        <ul>
            <li><a href="https://lydr.io">Home</a></li>
            <li><a href="/report">Report</a></li>
            <li><a href="https://lydr.io/about">About</a></li>
        </ul>
    </footer>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        

        var fileInput = document.getElementById('fileInput');
        var customButton = document.getElementById('customButton');
        var uploadForm = document.getElementById('uploadForm');

        customButton.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            customButton.textContent = this.files[0].name;
        });

        // Drag and drop functionality
        uploadForm.addEventListener('dragover', function(event) {
            event.preventDefault();
            uploadForm.classList.add('dragover');
        });

        uploadForm.addEventListener('dragleave', function(event) {
            event.preventDefault();
            uploadForm.classList.remove('dragover');
        });

        uploadForm.addEventListener('drop', function(event) {
            event.preventDefault();
            uploadForm.classList.remove('dragover');
            fileInput.files = event.dataTransfer.files;
            customButton.textContent = fileInput.files[0].name;
        });

        uploadForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const file = fileInput.files[0];
            if (!file) return; // Ensure a file is selected

            const formData = new FormData();
            formData.append('file', file);

            const xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', updateProgress);
            xhr.open('POST', '', true); // Make sure your server handles the POST request correctly
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('progressContainer').style.display = 'none';
                    document.body.innerHTML = xhr.responseText; // Replace the body content with the server response
                } else {
                    document.getElementById('progressBar').innerText = 'Upload failed';
                    document.getElementById('progressBar').style.backgroundColor = 'red';
                }
            };

            document.getElementById('progressContainer').style.display = 'block';
            xhr.send(formData);
        });

        function updateProgress(event) {
            if (event.lengthComputable) {
                const percentComplete = (event.loaded / event.total) * 100;
                document.getElementById('progressBar').style.width = percentComplete + '%';
                document.getElementById('progressBar').innerText = Math.round(percentComplete) + '%';
            }
        }
    });

    function copyUrl(url) {
        navigator.clipboard.writeText(url).then(() => {
            document.getElementById('message').innerText = 'URL copied to clipboard';
        }).catch(err => {
            document.getElementById('message').innerText = 'Failed to copy URL';
            document.getElementById('message').style.color = 'red';
        });
    }
    function shareUrl(url, name) {
        if (navigator.share) {
            navigator.share({
                title: 'dl.lydr |'.concat(" ", name),
                url: url
            }).catch(err => {
                console.log('Error sharing:', err);
            });
        } else {
            alert('Share not supported on this browser');
        }
    }
    </script>
</body>
</html>
HTML;

// Output the HTML content
echo $html;
?>

    