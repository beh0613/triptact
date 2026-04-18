<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
session_start();

// Database connection
$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";

// Create connection
$conn = new mysqli($host, $username, $password, $database);


// Get collection_id and date from URL parameters
$collection_id = $_GET['collection_id'] ?? null;
$date = $_GET['date'] ?? null; // Optional date parameter

if ($collection_id) {
    $query = "
        SELECT 
            s.state_name
        FROM collection c
        LEFT JOIN attractions a ON c.attraction_id = a.attraction_id
        LEFT JOIN hotels h ON c.hotel_id = h.hotel_id
        LEFT JOIN states s ON (a.state_id = s.state_id OR h.state_id = s.state_id)
        WHERE c.collection_id = ? 
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $collection_id); // Bind collection_id
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the state_name
    if ($row = $result->fetch_assoc()) {
        $state_name = $row['state_name'];
        $weather_data = fetchWeather($state_name); // Get current weather

        // If a date is specified, get forecast (this could be 5 days, etc.)
        if ($date) {
            $forecast_data = fetchForecast($state_name, $date); // This is for the forecast data
            echo json_encode(['forecast' => $forecast_data]);
        } else {
            // Return the current weather info
            echo json_encode([
                "state_name" => $state_name,
                "weather" => $weather_data
            ]);
        }
    } else {
        echo json_encode(["error" => "No data found for collection"]);
    }
} else {
    echo json_encode(["error" => "Collection ID is missing"]);
}
// Function to fetch weather data from OpenWeatherMap
function fetchWeather($state_name) {
    $api_key = '01c348787bf83521c5c58da319e62121';  // Updated API Key
    $state_name = urlencode($state_name);
    $api_url = "https://api.openweathermap.org/data/2.5/weather?q={$state_name}&appid={$api_key}&units=metric";
    $response = file_get_contents($api_url);
    
    if ($response === FALSE) {
        return null;
    }
    return json_decode($response, true);
}



?>