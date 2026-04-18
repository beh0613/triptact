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

// Create connection
$conn = new mysqli($host, $username, $password, $database);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    $emailOrUsername = $input['emailOrUsername'];
    $birthday = $input['birthday'];
    $gender = $input['gender'];

    // SQL to fetch the user record
    $sql = "SELECT id FROM Users WHERE (email = ? OR username = ?) AND birthday = ? AND gender = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $emailOrUsername, $emailOrUsername, $birthday, $gender);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['valid' => true]);
    } else {
        echo json_encode(['valid' => false]);
    }

    $stmt->close();
}

$conn->close();
?>