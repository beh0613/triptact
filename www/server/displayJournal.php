<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'false');
ini_set('session.cookie_path', '/');

// Set CORS headers
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, Delete, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

error_log("Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true)); // Log session data for debugging
// Database connection details
$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";



// Create connection
$conn = new mysqli($host, $username, $password, $database);


// Check user login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch journals
    fetchJournals($conn, $userId);
} 


// Fetch journals function
function fetchJournals($conn, $userId) {
    $sql = "SELECT * FROM journal WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $journals = $result->fetch_all(MYSQLI_ASSOC);

    if ($journals) {
        echo json_encode(['success' => true, 'data' => $journals]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No records found']);
    }
}
// Check for force delete action
if (isset($_GET['action']) && $_GET['action'] == 'force_delete' && isset($_GET['journal_id'])) {
    $journal_id = $_GET['journal_id']; // Get journal_id from request

    // Force delete the journal entry from the database
    $query = "DELETE FROM journal WHERE journal_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $journal_id); // Bind journal_id as an integer

    if ($stmt->execute()) {
        echo json_encode(["success" => true]); // Notify success
    } else {
        echo json_encode(["success" => false, "error" => "Failed to delete journal"]); // Notify failure
    }
    exit();
}


// Close the connection at the end of the script
$conn->close();



?>