<?php

// Start the session and allow necessary headers


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


// Check user login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];



// Retrieve journal_id from GET or POST request
$journalId = isset($_GET['journal_id']) ? $_GET['journal_id'] : null;

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $journalId) {
    // Retrieve journal data from database for GET request
    $query = "SELECT * FROM journal WHERE journal_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $journalId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data); // Send the fetched data as JSON
    } else {
        echo json_encode(['error' => 'Journal entry not found.']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['journal_id'])) {
    // Update journal data in database for POST request (when editing)
    $journalId = $_POST['journal_id'];
    $travelDate = $_POST['travel_date'];
    $feeling = $_POST['feeling'];
    $impression = $_POST['impression'];
    $foodSpending = $_POST['food_spending'];
    $transportSpending = $_POST['transport_spending'];
    $otherSpending = $_POST['other_spending'];
    $spendingAmount = $_POST['spending_amount'];
    
    $spendingCurrency = $_POST['spending_currency'];
    $convertedAmount = $_POST['converted_amount'];
    $convertedCurrency = $_POST['converted_currency'];
    $imageName = isset($_POST['image_name']) ? $_POST['image_name'] : null;
    
    // Handle the image upload (optional, if new image is uploaded)
   if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] === UPLOAD_ERR_OK) {
    $base_url = 'https://triptact.cmsa.digital/';
    $uploadDir = 'uploads/';

    // Validate file type and size
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES['image_path']['type'], $allowedMimeTypes)) {
        die('Error: Invalid file type.');
    }

    if ($_FILES['image_path']['size'] > 2097152) { // 2MB limit
        die('Error: File size exceeds 2MB.');
    }

    // Securely generate file path
    $fileName = basename($_FILES['image_path']['name']);
    $targetFile = $uploadDir . $fileName;
    $finalPath = $base_url . $targetFile; // Save full URL in the database

    if (move_uploaded_file($_FILES['image_path']['tmp_name'], $targetFile)) {
        // File successfully uploaded
        $imagePath = $finalPath;
    } else {
        die('Error: File upload failed.');
    }
} else {
    // No new file uploaded, retrieve the existing image path
    $query = "SELECT image_path FROM journal WHERE journal_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('i', $journalId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $currentData = $result->fetch_assoc();
            $imagePath = $currentData['image_path']; // Use the existing path
        } else {
            die('Error: Failed to retrieve current image.');
        }
        $stmt->close();
    } else {
        die('Error: Database query failed.');
    }
}


    // Update the journal entry in the database
    $updateQuery = "UPDATE journal SET
        travel_date = ?,
        feeling = ?,
        impression = ?,
        food_spending = ?,
        transport_spending = ?,
        other_spending = ?,
        spending_amount = ?,
        spending_currency = ?,
        converted_amount = ?,
        converted_currency = ?,
        image_path = ?
        WHERE journal_id = ?";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('ssssdddsdssi', $travelDate, $feeling, $impression, $foodSpending, $transportSpending,
                    $otherSpending, $spendingAmount, $spendingCurrency, $convertedAmount, $convertedCurrency, $imagePath, $journalId);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Journal updated successfully!']);
    } else {
        echo json_encode(['error' => 'Failed to update the journal.']);
    }
} else {
    // If missing journal_id or invalid request
    echo json_encode(['error' => 'Invalid request method or missing journal_id.']);
}

$stmt->close();
$conn->close();

?>