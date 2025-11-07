<?php
require_once 'config.php';
$conn = getDBConnection();

// Get filter parameters
$room_type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 10000;

// Build query - Show all rooms regardless of status
$query = "SELECT * FROM rooms WHERE 1=1";

// Apply room type filter
if ($room_type) {
    $query .= " AND room_type = '" . $conn->real_escape_string($room_type) . "'";
}

// Apply price filter
$query .= " AND price <= " . (int)$max_price;

// Order by price
$query .= " ORDER BY price ASC";

$rooms_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Rooms - BookHaven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Page Header -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold">Our Rooms</h1>
                    <p class="lead">Find your perfect accommodation</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Filter Rooms</h5>
                        <form method="GET" action="">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Room Type</label>
                                <select name="type" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    <option value="Single" <?php echo $room_type == 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Double" <?php echo $room_type == 'Double' ? 'selected' : ''; ?>>Double</option>
                                    <option value="Deluxe" <?php echo $room_type == 'Deluxe' ? 'selected' : ''; ?>>Deluxe</option>
                                    <option value="Family" <?php echo $room_type == 'Family' ? 'selected' : ''; ?>>Family</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Max Price: <?php echo formatCurrency($max_price); ?></label>
                                <input type="range" class="form-range" name="max_price" min="1000" max="10000" step="500" value="<?php echo $max_price; ?>" oninput="this.nextElementSibling.textContent = '₱' + this.value.toLocaleString()">
                                <div class="text-center text-muted">₱<?php echo number_format($max_price); ?></div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            <a href="rooms.php" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                        </form>
                    </div>
                </div>

                <!-- Room Features -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Room Amenities</h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Free WiFi</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Air Conditioning</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Flat Screen TV</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Mini Fridge</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>24/7 Room Service</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Daily Housekeeping</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Rooms Grid -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Available Rooms (<?php echo $rooms_result->num_rows; ?>)</h4>
                </div>

                <?php if ($rooms_result->num_rows > 0): ?>
                    <div class="row">
                        <?php while($room = $rooms_result->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card room-card h-100 shadow-sm">
                                <div class="room-image-wrapper">
                                    <img src="<?php echo $room['image']; ?>" class="card-img-top room-image" alt="<?php echo $room['room_type']; ?>">
                                    <div class="room-badge"><?php echo $room['room_type']; ?></div>
                                    <!-- Status badge -->
                                    <?php if ($room['status'] === 'occupied'): ?>
                                        <div class="badge bg-danger position-absolute top-0 end-0 m-2">Occupied</div>
                                    <?php elseif ($room['status'] === 'maintenance'): ?>
                                        <div class="badge bg-warning position-absolute top-0 end-0 m-2">Maintenance</div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title fw-bold"><?php echo $room['room_type']; ?> Room - <?php echo $room['room_number']; ?></h5>
                                    <p class="card-text text-muted"><?php echo $room['description']; ?></p>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">Amenities:</small>
                                        <small class="text-primary"><?php echo $room['amenities']; ?></small>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <i class="bi bi-people-fill text-primary me-2"></i>
                                            <small>Capacity: <?php echo $room['capacity']; ?> guest(s)</small>
                                        </div>
                                        <div>
                                            <?php if ($room['status'] === 'available'): ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php elseif ($room['status'] === 'occupied'): ?>
                                                <span class="badge bg-danger">Occupied</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Maintenance</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="price">
                                            <h4 class="text-primary mb-0"><?php echo formatCurrency($room['price']); ?></h4>
                                            <small class="text-muted">per night</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <?php if ($room['status'] !== 'available'): ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="bi bi-x-circle me-2"></i>Not Available
                                        </button>
                                    <?php elseif (isLoggedIn() && isGuest()): ?>
                                        <a href="book-room.php?room_id=<?php echo $room['id']; ?>" class="btn btn-primary w-100">
                                            <i class="bi bi-calendar-check me-2"></i>Book Now
                                        </a>
                                    <?php elseif (isLoggedIn() && isAdmin()): ?>
                                        <button class="btn btn-secondary w-100" disabled>Admin Account</button>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Book
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No rooms found matching your criteria. Please try different filters.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>