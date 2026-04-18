<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Database credentials
$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Check if the email exists
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(200); // OK
        echo json_encode([
            "success" => true,
            "emailAvailable" => false,
            "message" => "Email is already registered. Please log in with this email."
        ]);
    } else {
        http_response_code(200); // OK
        echo json_encode([
            "success" => true,
            "emailAvailable" => true,
            "message" => "Email is available."
        ]);
    }

    $stmt->close();
} else {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Invalid request. Email parameter is required."]);
}

$conn->close();
?>
