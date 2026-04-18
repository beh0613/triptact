<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'true');
ini_set('session.cookie_path', '/');

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();


// Database connection details
$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";
// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $birthday = $_POST['birthday'] ?? '';
    $password = $_POST['password'] ?? '';
    $gender = $_POST['gender'] ?? '';

    // Validate input (basic validation example)
    if (empty($email) || empty($username) || empty($birthday) || empty($password) || empty($gender)) {
        http_response_code(400); // Bad Request
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("INSERT INTO Users (email, username, birthday, password, gender) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $username, $birthday, $hashed_password, $gender);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode(["success" => true, "message" => "New record created successfully"]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>