<?php
require_once 'config.php';
$conn = getDBConnection();

// Get featured rooms
$rooms_query = "SELECT * FROM rooms WHERE status = 'available' LIMIT 4";
$rooms_result = $conn->query($rooms_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookHaven - Hotel Reservation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    .hero-section {
        position: relative;
        height: 600px;
        background-image: url('assets/images/BookHaven.jpg?v=2') !important;
        background-size: cover !important;
        background-position: center !important;
        background-attachment: fixed !important;
    }
    
</style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay">
            <div class="container">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8">
                        <h1 class="display-3 fw-bold text-white mb-4">Welcome to BookHaven</h1>
                        <p class="lead text-white mb-5">Experience luxury and comfort in the heart of the city. Book your perfect stay with us today.</p>
                        <a href="rooms.php" class="btn btn-primary btn-lg px-5 py-3">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="feature-box p-4">
                        <i class="bi bi-wifi fs-1 text-primary mb-3"></i>
                        <h4>Free WiFi</h4>
                        <p>Stay connected with high-speed internet throughout your stay.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box p-4">
                        <i class="bi bi-shield-check fs-1 text-primary mb-3"></i>
                        <h4>Secure Booking</h4>
                        <p>Safe and secure online reservation system for your peace of mind.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box p-4">
                        <i class="bi bi-headset fs-1 text-primary mb-3"></i>
                        <h4>24/7 Support</h4>
                        <p>Our dedicated team is always here to assist you.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Rooms -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Rooms</h2>
                <p class="lead text-muted">Choose from our selection of comfortable and luxurious rooms</p>
            </div>
            <div class="row">
                <?php while($room = $rooms_result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card room-card h-100 shadow-sm">
                        <div class="room-image-wrapper">
                            <img src="<?php echo $room['image']; ?>" class="card-img-top room-image" alt="<?php echo $room['room_type']; ?>">
                            <div class="room-badge"><?php echo $room['room_type']; ?></div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $room['room_type']; ?> Room</h5>
                            <p class="card-text text-muted small"><?php echo substr($room['description'], 0, 80); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="price">
                                    <small class="text-muted">From</small>
                                    <h5 class="text-primary mb-0"><?php echo formatCurrency($room['price']); ?></h5>
                                    <small class="text-muted">per night</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="rooms.php" class="btn btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-4">
                <a href="rooms.php" class="btn btn-primary btn-lg">View All Rooms</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h2 class="display-6 fw-bold mb-4">About BookHaven</h2>
                    <p class="lead">Your home away from home in the heart of the city.</p>
                    <p>BookHaven offers a perfect blend of comfort, luxury, and convenience. Our modern facilities and exceptional service ensure that every guest enjoys a memorable stay. Whether you're traveling for business or leisure, we have the perfect accommodation for you.</p>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Prime Location</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Modern Amenities</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Exceptional Service</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Competitive Rates</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <img src="assets/images/BookHaven.jpg" alt="BookHaven Hotel" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>