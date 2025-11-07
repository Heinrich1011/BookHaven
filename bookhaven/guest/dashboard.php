<?php
require_once '../config.php';

if (!isLoggedIn() || !isGuest()) {
    redirect('login.php');
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get booking statistics
$stats_query = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM bookings WHERE user_id = ?";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get recent bookings
$bookings_query = "SELECT b.*, r.room_type, r.room_number 
                   FROM bookings b 
                   JOIN rooms r ON b.room_id = r.id 
                   WHERE b.user_id = ? 
                   ORDER BY b.created_at DESC LIMIT 5";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - BookHaven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-12 mb-4">
                <h2 class="fw-bold">Welcome back, <?php echo $_SESSION['name']; ?>!</h2>
                <p class="text-muted">Manage your bookings and profile</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="dashboard-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?php echo $stats['total_bookings']; ?></h3>
                                <small class="text-muted">Total Bookings</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="dashboard-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?php echo $stats['pending']; ?></h3>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="dashboard-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?php echo $stats['confirmed']; ?></h3>
                                <small class="text-muted">Confirmed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="dashboard-icon bg-info bg-opacity-10 text-info me-3">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?php echo $stats['completed']; ?></h3>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <a href="../rooms.php" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-plus text-primary" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Book a Room</h5>
                            <p class="text-muted">Browse available rooms</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="my-bookings.php" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-list-check text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">My Bookings</h5>
                            <p class="text-muted">View booking history</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="profile.php" class="text-decoration-none">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-person-gear text-warning" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">My Profile</h5>
                            <p class="text-muted">Update your information</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Recent Bookings</h5>
                <?php if ($bookings->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Room</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo $booking['room_type']; ?> - <?php echo $booking['room_number']; ?></td>
                                    <td><?php echo formatDate($booking['check_in']); ?></td>
                                    <td><?php echo formatDate($booking['check_out']); ?></td>
                                    <td><?php echo formatCurrency($booking['total_price']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="my-bookings.php" class="btn btn-outline-primary">View All Bookings</a>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        You haven't made any bookings yet. <a href="../rooms.php" class="alert-link">Book your first room now!</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>