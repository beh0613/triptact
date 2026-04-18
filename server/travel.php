<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Database connection details
$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";

// Create connection
$conn = new mysqli($host, $username, $password, $database);


if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$country = $input['country'] ?? '';

if (empty($country)) {
    echo json_encode(["exists" => false, "error" => "Country name not provided"]);
    exit();
}

$sql = "SELECT * FROM countries WHERE country_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $country);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["exists" => true]);
} else {
    echo json_encode(["exists" => false]);
}

$stmt->close();
$conn->close();
?>