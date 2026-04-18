<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', 'true');
ini_set('session.cookie_path', '/');
// Set CORS headers
// Set allowed origins dynamically
$allowedOrigins = [
    'https://triptact.cmsa.digital',    // Web app
    'chrome-extension://ckejmhbmlajgoklhgbapkiccekfoccmk', // Web Extension Origin
    'capacitor://',  // Added for Cordova/Capacitor
    'capacitor://localhost',            // APK Origin
    'file://',                          // File-based apps (Cordova)
    'app://',  
    'http://localhost',                 // Development environment
];

// Check and set the Origin dynamically
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} else {
    // Optional: Default origin for unmatched cases (secure usage requires restricting this in production)
    header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
}

// Required headers for credentials
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
error_log("Session ID at profile: " . session_id() . ", User ID: " . ($_SESSION['user_id'] ?? 'not set'));
// Database connection details
$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";
// Create a connection to the database
$conn = new mysqli($host, $username, $password, $database);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check for POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    // SQL query to get the user data based on the email
    $sql = "SELECT id, password FROM Users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    // If a user with the provided email exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashedPassword);
        $stmt->fetch();
        // Verify the password with the hashed password stored in the database
       // Successful login response
if (password_verify($password, $hashedPassword)) {
    $_SESSION['user_id'] = $id;
    echo json_encode([
        "success" => true,
        "message" => "Login successful!",
        "user_id" => $id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Invalid password. Please try again."
    ]);
}

    } else {
        echo json_encode(["error" => "No user found with that email. Please try again."]);
    }
    $stmt->close();
}
$conn->close();
?> 