<?php
function generateRandomHex($length = 32) {
    // Initialize the random string variable
    $randomString = '';
    
    // Define the characters that are allowed in a hex string
    $hexCharacters = '0123456789abcdef';

    // Loop through and append a random character from $hexCharacters to $randomString
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $hexCharacters[rand(0, strlen($hexCharacters) - 1)];
    }

    return $randomString;
}

// Example usage
echo generateRandomHex(); // Outputs a random hex string of 16 characters
?>
