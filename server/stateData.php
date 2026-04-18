<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database connection
$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";

// Create connection
$conn = new mysqli($host, $username, $password, $database);


if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Get country from POST
$input = json_decode(file_get_contents('php://input'), true);
$country = $input['country'] ?? '';

if (empty($country)) {
    echo json_encode(["states" => []]);
    exit();
}

// Query to get country ID
$countryQuery = "SELECT country_id FROM countries WHERE country_name = ?";
$stmt = $conn->prepare($countryQuery);
$stmt->bind_param("s", $country);
$stmt->execute();
$countryResult = $stmt->get_result();

if ($countryResult->num_rows > 0) {
    $countryData = $countryResult->fetch_assoc();

    // Query to fetch states
    $stateQuery = "SELECT state_name AS name, imageS AS image FROM states WHERE country_id = ?";
    $stmt = $conn->prepare($stateQuery);
    $stmt->bind_param("s", $countryData['country_id']);
    $stmt->execute();
    $stateResult = $stmt->get_result();

    $states = [];
    while ($row = $stateResult->fetch_assoc()) {
        $states[] = $row;
    }

    echo json_encode(["states" => $states]);
} else {
    echo json_encode(["states" => []]);
}

$stmt->close();
$conn->close();
?>
