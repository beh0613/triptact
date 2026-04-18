<?php

ob_start(); // Start output buffering
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


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in."]);
    exit;
}

// Fetch form data
$id = $_SESSION['user_id']; // Assuming the user is logged in
$place_name = $_POST['place_name'] ?? '';
$state = $_POST['state'] ?? '';
$country = $_POST['country'] ?? '';
$travel_date = $_POST['travel_date'] ?? '';
$feeling = $_POST['feeling'] ?? '';
$impression = $_POST['impression'] ?? '';
$spending_amount = $_POST['spending_amount'] ?? 0;
$spending_currency = $_POST['spending_currency'] ?? '';
$converted_amount = $_POST['converted_amount'] ?? 0;
$converted_currency = $_POST['converted_currency'] ?? '';
$image = $_FILES['image_path'] ?? null; // Retrieve the uploaded image directly
$food_spending = (int) ($_POST['food_spending'] ?? 0);
$transport_spending = (int) ($_POST['transport_spending'] ?? 0);
$other_spending = (int) ($_POST['other_spending'] ?? 0);
$collection_id = $_POST['collection_id'] ?? null;

// Validate mandatory fields
if (!$place_name || !$state || !$country || !$travel_date) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

// Define the base URL
$base_url = 'https://triptact.cmsa.digital/'; // Base URL for your server

// Image file processing
$image_path = null;
if ($image && $image['tmp_name']) {
    // Path to the `uploads/` folder
    $upload_dir = 'uploads/'; // Relative path to uploads directory

    // Check if the uploads folder exists
    if (!file_exists($upload_dir)) {
        echo json_encode(["error" => "Uploads folder does not exist"]);
        exit;
    }

    $image_name = preg_replace("/[^a-zA-Z0-9_-]/", "", basename($image['name'])); // Sanitize the filename
    $unique_name = $image_name . '_' . uniqid() . '.png'; // Unique filename
    $relative_path = $upload_dir . $unique_name; // Create the relative file path
    $absolute_path = $base_url . $relative_path; // Full URL including base path

    if (!move_uploaded_file($image['tmp_name'], $relative_path)) {
        echo json_encode(["error" => "Failed to save image"]);
        exit;
    }

    $image_path = $absolute_path; // Assign the full URL for response or further processing
} else {
    echo json_encode(["error" => "No image uploaded"]);
    exit;
}

// Return the full image URL
echo json_encode(["success" => true, "image_url" => $image_path]);

// Insert query with spending fields
$query = "INSERT INTO journal (
    id, place_name, state, country, travel_date, feeling, impression, 
    spending_amount, spending_currency, converted_amount, converted_currency, image_path, food_spending, transport_spending, other_spending
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);

// Debug values to ensure proper passing of data


$stmt->bind_param("issssssdsdssiii", $id, $place_name, $state, $country, $travel_date, $feeling, $impression, $spending_amount, $spending_currency, $converted_amount, $converted_currency, $image_path, $food_spending, $transport_spending, $other_spending);

if ($stmt->execute()) {
    // Delete collection_id from collection table if provided
    if ($collection_id) {
        $delete_query = "DELETE FROM collection WHERE collection_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $collection_id);
        if (!$delete_stmt->execute()) {
            echo json_encode(["error" => "Failed to delete collection entry."]);
            $delete_stmt->close();
            exit;
        }
        $delete_stmt->close();
    }

    // Respond with success and redirect URL
    echo json_encode(["success" => true, "redirect" => "displayJournal.html"]);
} else {
    echo json_encode(["error" => "Failed to insert journal data."]);
}
$stmt->close();
$conn->close();
ob_end_flush(); // Ensure the response is sent

?>
