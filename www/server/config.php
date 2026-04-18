<?php
// config.php - Create this file to store shared configuration
$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";

// Set secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);  // Enable if using HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.save_path', '/tmp');
ini_set('session.cookie_lifetime', 3600);

// Shared CORS configuration function
function setCORSHeaders() {
    // Replace * with your actual frontend domain in production
    $allowed_origin = "YOUR_FRONTEND_DOMAIN";
    
    if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] == $allowed_origin) {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
        header("Access-Control-Allow-Credentials: true");
    }
    
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}
