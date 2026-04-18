<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'true');
ini_set('session.cookie_path', '/');

// Set CORS headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

error_log("Session ID at profile: " . session_id() . ", User ID: " . ($_SESSION['user_id'] ?? 'not set'));

$host = "localhost";
$username = "u237859360_triptact";
$password = "Triptact123@";
$database = "u237859360_triptact";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => true, "message" => "Database connection failed: " . $conn->connect_error]));
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => true, "message" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? '';
$category = $_GET['category'] ?? '';
$response = array();

try {
    // First, get the item details
    if ($category === 'hotel') {
        $stmt = $conn->prepare("SELECT * FROM hotels WHERE hotel_id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $itemDetails = [
                "name" => $row['hotel_name'],
                "address" => $row['address'],
                "star_rating" => $row['star_rating'],
                "images" => $row['image'],
                "website" => $row['website'],
                "price_range" => $row['price_range'],
                "nearby_attraction" => $row['nearbyAttraction'],
                "description" => $row['description']
            ];
        } else {
            throw new Exception("Hotel not found.");
        }
        $stmt->close();
    } elseif ($category === 'attraction') {
        $stmt = $conn->prepare("SELECT * FROM attractions WHERE attraction_id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $itemDetails = [
                "name" => $row['attraction_name'],
                "description" => $row['description'],
                "category" => $row['category'],
                "location" => $row['location'],
                "opening_hours" => $row['opening_hours'],
                "entrance_fee" => $row['entrance_fee'],
                "nearby_attraction" => $row['nearby_attraction'],
                "contact_detail" => $row['contact_detail'],
                "rating" => $row['rating'],
                "image" => $row['imageD'],
                "website" => $row['website']
            ];
        } else {
            throw new Exception("Attraction not found.");
        }
        $stmt->close();
    } else {
        throw new Exception("Invalid category specified.");
    }

    // Handle POST request for adding to collection
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $existing_check_query = ($category === 'hotel') 
            ? "SELECT * FROM collection WHERE id = ? AND hotel_id = ?"
            : "SELECT * FROM collection WHERE id = ? AND attraction_id = ?";

        $stmt = $conn->prepare($existing_check_query);
        $stmt->bind_param("is", $user_id, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $response = [
                'error' => true,
                'message' => "This item is already in your collection.",
                'details' => $itemDetails
            ];
        } else {
            // Add to collection
            $insert_query = ($category === 'hotel')
                ? "INSERT INTO collection (id, hotel_id) VALUES (?, ?)"
                : "INSERT INTO collection (id, attraction_id) VALUES (?, ?)";

            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("is", $user_id, $id);

            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => ($category === 'hotel' 
                        ? "Hotel successfully added to your collection!" 
                        : "Attraction successfully added to your collection!"),
                    'details' => $itemDetails
                ];
            } else {
                throw new Exception("Failed to add the item to your collection.");
            }
            $stmt->close();
        }
    } else {
        // For GET requests, just return the details
        $response = [
            'success' => true,
            'details' => $itemDetails
        ];
    }

} catch (Exception $e) {
    $response = [
        'error' => true,
        'message' => $e->getMessage(),
        'details' => $itemDetails ?? null
    ];
} 

    // Send single JSON response
    echo json_encode($response);
    exit;

?>