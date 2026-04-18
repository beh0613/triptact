<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'true');
ini_set('session.cookie_path', '/');

// Set CORS headers for your specific domain
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Database connection details - updated to match your triptact database
$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check user login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $jsonInput = file_get_contents('php://input');
    
    // Log the received data for debugging
    error_log("Received data: " . $jsonInput);
    
    $data = json_decode($jsonInput, true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
        exit;
    }

    $username = $data['username'] ?? null;
    $birthday = $data['birthday'] ?? null;
    $gender = $data['gender'] ?? null;

    // Log the extracted values
    error_log("Username: $username, Birthday: $birthday, Gender: $gender");

    if (!$username || !$birthday || !$gender) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    // Update user profile in database
    $updateQuery = "UPDATE Users SET 
        username = ?,
        birthday = ?,
        gender = ?
        WHERE id = ?";
    
    $stmt = $conn->prepare($updateQuery);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Query preparation failed']);
        exit;
    }

    $stmt->bind_param('sssi', $username, $birthday, $gender, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update profile: ' . $stmt->error]);
    }

    $stmt->close();
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request to fetch current profile
    $query = "SELECT username, birthday, gender FROM Users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $userData]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to fetch profile']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();
?>