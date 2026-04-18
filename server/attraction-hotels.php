<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


// Database connection details
$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";


try {
    // Establish database connection
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Decode incoming JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $state = trim($input['state'] ?? '');
    $country = trim($input['country'] ?? '');

    if (empty($country)) {
        throw new Exception("Country is required.");
    }

    // Prepare the response structure
    $response = [];

    // Optional state condition
    $stateCondition = !empty($state) ? "AND states.state_name LIKE ?" : "";

    // Query for hotels
    $hotelsQuery = "
        SELECT 
            states.state_name,
            states.state_id,
            hotels.hotel_id,
            hotels.hotel_name,
            hotels.address,
            hotels.star_rating,
            hotels.image,
            hotels.website,
            hotels.price_range,
            hotels.nearbyAttraction
        FROM countries
        JOIN states ON countries.country_id = states.country_id
        LEFT JOIN hotels ON states.state_id = hotels.state_id
        WHERE countries.country_name = ?
        $stateCondition
        ORDER BY hotels.hotel_id DESC
    ";

    $hotelsStmt = $conn->prepare($hotelsQuery);
    if (!empty($state)) {
        $likeState = "%" . $state . "%";
        $hotelsStmt->bind_param("ss", $country, $likeState);
    } else {
        $hotelsStmt->bind_param("s", $country);
    }
    $hotelsStmt->execute();
    $hotelsResult = $hotelsStmt->get_result();

    // Process hotels data
    while ($row = $hotelsResult->fetch_assoc()) {
        $stateName = $row['state_name'];
        if (!isset($response[$stateName])) {
            $response[$stateName] = ['hotels' => [], 'attractions' => []];
        }

        if ($row['hotel_id']) { // Add hotel only if an ID exists
            $response[$stateName]['hotels'][] = [
                'hotel_id' => $row['hotel_id'],
                'hotel_name' => $row['hotel_name'],
                'address' => $row['address'],
                'star_rating' => $row['star_rating'],
                'image' => $row['image'],
                'website' => $row['website'],
                'price_range' => $row['price_range'],
                'nearbyAttraction' => $row['nearbyAttraction']
            ];
        }
    }

    // Query for attractions
    $attractionsQuery = "
        SELECT 
            states.state_name,
            attractions.attraction_id,
            attractions.state_id,
            attractions.attraction_name,
            attractions.description,
            attractions.category,
            attractions.location,
            attractions.opening_hours,
            attractions.entrance_fee,
            attractions.nearby_attraction,
            attractions.contact_detail,
            attractions.rating,
            attractions.imageD,
            attractions.website
        FROM countries
        JOIN states ON countries.country_id = states.country_id
        LEFT JOIN attractions ON states.state_id = attractions.state_id
        WHERE countries.country_name = ?
        $stateCondition
        ORDER BY attractions.attraction_id ASC
    ";

    $attractionsStmt = $conn->prepare($attractionsQuery);
    if (!empty($state)) {
        $attractionsStmt->bind_param("ss", $country, $likeState);
    } else {
        $attractionsStmt->bind_param("s", $country);
    }
    $attractionsStmt->execute();
    $attractionsResult = $attractionsStmt->get_result();

    // Process attractions data
    while ($row = $attractionsResult->fetch_assoc()) {
        $stateName = $row['state_name'];
        if (!isset($response[$stateName])) {
            $response[$stateName] = ['hotels' => [], 'attractions' => []];
        }

        $response[$stateName]['attractions'][] = [
            'attraction_id' => $row['attraction_id'],
            'attraction_name' => $row['attraction_name'],
            'description' => $row['description'],
            'category' => $row['category'],
            'location' => $row['location'],
            'opening_hours' => $row['opening_hours'],
            'entrance_fee' => $row['entrance_fee'],
            'nearby_attraction' => $row['nearby_attraction'],
            'contact_detail' => $row['contact_detail'],
            'rating' => $row['rating'],
            'imageD' => $row['imageD'],
            'website' => $row['website']
        ];
    }

    // Output the results as JSON
    echo json_encode($response);
} catch (Exception $e) {
    // Handle errors by returning JSON response
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    // Clean up
    if (isset($hotelsStmt)) $hotelsStmt->close();
    if (isset($attractionsStmt)) $attractionsStmt->close();
    if (isset($conn)) $conn->close();
}