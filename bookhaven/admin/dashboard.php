<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$conn = getDBConnection();

// Get statistics
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'];
$available_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms WHERE status = 'available'")->fetch_assoc()['count'];
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$pending_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch_assoc()['count'];
$total_guests = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'guest'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT COALESCE(SUM(total_price), 0) as revenue FROM bookings WHERE status IN ('confirmed', 'completed')")->fetch_assoc()['revenue'];

// Get recent bookings
$recent_bookings = $conn->query("SELECT b.*, u.name as guest_name, r.room_type, r.room_number 
                                FROM bookings b 
                                JOIN users u ON b.user_id = u.id 
                                JOIN rooms r ON b.room_id = r.id 
                                ORDER BY b.created_at DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BookHaven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 admin-sidebar p-0">
                <div class="p-3">
                    <h5 class="fw-bold mb-4">Admin Panel</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-rooms.php">
                                <i class="bi bi-door-open me-2"></i>Rooms
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-bookings.php">
                                <i class="bi bi-calendar-check me-2"></i>Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="bi bi-people me-2"></i>Guests
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold">Dashboard</h2>
                        <p class="text-muted">Welcome back, <?php echo $_SESSION['name']; ?></p>
                    </div>
                    <div>
                        <span class="text-muted"><i class="bi bi-calendar me-2"></i><?php echo date('F d, Y'); ?></span>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card shadow-sm border-start border-primary border-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="dashboard-icon bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-door-open"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?php echo $total_rooms; ?></h4>
                                        <small class="text-muted">Total Rooms</small>
                                        <div class="small text-success mt-1">
                                            <i class="bi bi-check-circle-fill me-1"></i><?php echo $available_rooms; ?> Available
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card shadow-sm border-start border-success border-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="dashboard-icon bg-success bg-opacity-10 text-success me-3">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?php echo $total_bookings; ?></h4>
                                        <small class="text-muted">Total Bookings</small>
                                        <div class="small text-warning mt-1">
                                            <i class="bi bi-clock-history me-1"></i><?php echo $pending_bookings; ?> Pending
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card shadow-sm border-start border-info border-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="dashboard-icon bg-info bg-opacity-10 text-info me-3">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?php echo $total_guests; ?></h4>
                                        <small class="text-muted">Registered Guests</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card shadow-sm border-start border-warning border-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="dashboard-icon bg-warning bg-opacity-10 text-warning me-3">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?php echo formatCurrency($total_revenue); ?></h4>
                                        <small class="text-muted">Total Revenue</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Recent Bookings</h5>
                            <a href="manage-bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $booking['id']; ?></td>
                                        <td>
                                            <i class="bi bi-person-circle me-1"></i>
                                            <?php echo $booking['guest_name']; ?>
                                        </td>
                                        <td><?php echo $booking['room_type']; ?> - <?php echo $booking['room_number']; ?></td>
                                        <td><?php echo formatDate($booking['check_in']); ?></td>
                                        <td><?php echo formatDate($booking['check_out']); ?></td>
                                        <td class="fw-bold"><?php echo formatCurrency($booking['total_price']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($booking['status'] === 'pending'): ?>
                                            <a href="manage-bookings.php?action=confirm&id=<?php echo $booking['id']; ?>" 
                                               class="btn btn-sm btn-success" title="Confirm">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="manage-bookings.php?view=<?php echo $booking['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>