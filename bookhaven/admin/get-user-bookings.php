<?php
require_once '../config.php';

// Check if admin is logged in
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$conn = getDBConnection();

if (isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    
    $query = "SELECT b.*, r.room_type, r.room_number 
              FROM bookings b 
              JOIN rooms r ON b.room_id = r.id 
              WHERE b.user_id = ? 
              ORDER BY b.created_at DESC 
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($bookings);
    
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user_id parameter']);
}

$conn->close();
?>