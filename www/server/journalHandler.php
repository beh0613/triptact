<?php
// Set headers for JSON response
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

// Check if the connection is established before proceeding
if (!$conn) {
    echo json_encode(["error" => "Failed to connect to the database."]);
    exit;
}

// Get the collection_id from GET parameters
$collection_id = $_GET['collection_id'] ?? null;

// Check if collection_id is provided
if ($collection_id) {
    // SQL query to fetch the data
    $query = "
        SELECT 
            s.state_name, 
            a.attraction_name, 
            co.country_name,
            h.hotel_name
        FROM collection c
        LEFT JOIN attractions a ON c.attraction_id = a.attraction_id
        LEFT JOIN hotels h ON c.hotel_id = h.hotel_id
        LEFT JOIN states s ON (a.state_id = s.state_id OR h.state_id = s.state_id)
        LEFT JOIN countries co ON (s.country_id = co.country_id)
        WHERE c.collection_id = ?
    ";

    // Prepare the query statement
    $stmt = $conn->prepare($query);

    if ($stmt) {
        // Bind the parameter and execute the query
        $stmt->bind_param("i", $collection_id); // "i" means integer
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the result has any rows
        if ($row = $result->fetch_assoc()) {
            echo json_encode([
                "state_name" => $row['state_name'],
                "attraction_name" => $row['attraction_name'],
                "country_name" => $row['country_name'],
                "hotel_name" => $row['hotel_name']
            ]);
        } else {
            // No matching data found for the collection_id
            echo json_encode(["error" => "No data found for the provided collection ID"]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(["error" => "Failed to prepare query"]);
    }
} else {
    // Missing collection_id in the request
    echo json_encode(["error" => "Collection ID is missing"]);
}

// Close the database connection (if needed)
$conn->close();
?>

