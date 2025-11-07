<?php
require_once 'config.php';

// Check if user is logged in and is a guest
if (!isLoggedIn() || !isGuest()) {
    redirect('login.php');
}

$conn = getDBConnection();
$error = '';
$success = '';

// Get room details
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$room_query = "SELECT * FROM rooms WHERE id = ? AND status = 'available'";
$stmt = $conn->prepare($room_query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room_result = $stmt->get_result();

if ($room_result->num_rows === 0) {
    redirect('rooms.php');
}

$room = $room_result->fetch_assoc();

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check_in = sanitize($_POST['check_in']);
    $check_out = sanitize($_POST['check_out']);
    $special_requests = sanitize($_POST['special_requests']);
    
    // Validate dates
    $today = date('Y-m-d');
    if ($check_in < $today) {
        $error = 'Check-in date cannot be in the past.';
    } elseif ($check_out <= $check_in) {
        $error = 'Check-out date must be after check-in date.';
    } else {
        // Calculate total price
        $days = calculateDays($check_in, $check_out);
        $total_price = $room['price'] * $days;
        
        // Insert booking
        $user_id = $_SESSION['user_id'];
        $insert_query = "INSERT INTO bookings (user_id, room_id, check_in, check_out, total_price, special_requests, status) 
                        VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iissds", $user_id, $room_id, $check_in, $check_out, $total_price, $special_requests);
        
        if ($stmt->execute()) {
            $success = 'Booking request submitted successfully! Our team will confirm your reservation shortly.';
        } else {
            $error = 'Booking failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room - BookHaven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="fw-bold mb-4">Complete Your Booking</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                                <div class="mt-3">
                                    <a href="guest/my-bookings.php" class="btn btn-primary">View My Bookings</a>
                                    <a href="rooms.php" class="btn btn-outline-secondary">Book Another Room</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="check_in" class="form-label fw-bold">Check-in Date</label>
                                        <input type="date" class="form-control" id="check_in" name="check_in" 
                                               min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="check_out" class="form-label fw-bold">Check-out Date</label>
                                        <input type="date" class="form-control" id="check_out" name="check_out" 
                                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="special_requests" class="form-label fw-bold">Special Requests (Optional)</label>
                                    <textarea class="form-control" id="special_requests" name="special_requests" 
                                              rows="3" placeholder="Any special requirements or requests..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-calendar-check me-2"></i>Confirm Booking
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="booking-summary">
                    <h5 class="fw-bold mb-3">Booking Summary</h5>
                    <img src="<?php echo $room['image']; ?>" class="img-fluid rounded mb-3" alt="Room">
                    <h6 class="fw-bold"><?php echo $room['room_type']; ?> Room</h6>
                    <p class="text-muted small"><?php echo $room['description']; ?></p>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Room Type:</span>
                        <span class="fw-bold"><?php echo $room['room_type']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Price per Night:</span>
                        <span class="fw-bold text-primary"><?php echo formatCurrency($room['price']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Capacity:</span>
                        <span class="fw-bold"><?php echo $room['capacity']; ?> guests</span>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Final price will be calculated based on your selected dates.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>