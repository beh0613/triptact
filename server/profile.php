<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'true');
ini_set('session.cookie_path', '/');

// Set CORS headers
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
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

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Check if the user is logged in by verifying the session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();  // Stop script execution if user is not logged in
}

$user_id = $_SESSION['user_id'];

// SQL query to fetch user details based on the user_id from the session
$sql = "SELECT username, email, birthday, gender FROM Users WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(["error" => "SQL preparation failed: " . $conn->error]));
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode($user);  // Send back the user's data as a JSON response
} else {
    echo json_encode(["error" => "User not found"]);
}

$stmt->close();
$conn->close();
?>