<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$conn = getDBConnection();
$message = '';

// Search functionality
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$query = "SELECT u.*, 
          COUNT(DISTINCT b.id) as total_bookings,
          SUM(CASE WHEN b.status = 'completed' THEN b.total_price ELSE 0 END) as total_spent
          FROM users u 
          LEFT JOIN bookings b ON u.id = b.user_id 
          WHERE u.role = 'guest'";

if ($search) {
    $query .= " AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%' OR u.phone LIKE '%$search%')";
}

$query .= " GROUP BY u.id ORDER BY u.created_at DESC";

$users = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Guests - BookHaven Admin</title>
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
                            <a class="nav-link" href="dashboard.php">
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
                            <a class="nav-link active" href="manage-users.php">
                                <i class="bi bi-people me-2"></i>Guests
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="fw-bold mb-4">Manage Guests</h2>

                <?php echo $message; ?>

                <!-- Search Bar -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-10">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by name, email, or phone..."
                                       value="<?php echo $search; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Search
                                </button>
                            </div>
                        </form>
                        <?php if ($search): ?>
                        <div class="mt-2">
                            <a href="manage-users.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Clear Search
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <?php
                    $stats_query = "SELECT 
                        COUNT(*) as total_guests,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_guests,
                        (SELECT COUNT(*) FROM bookings WHERE status IN ('pending', 'confirmed')) as active_bookings
                        FROM users WHERE role = 'guest'";
                    $stats = $conn->query($stats_query)->fetch_assoc();
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="dashboard-icon bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?php echo $stats['total_guests']; ?></h4>
                                        <small class="text-muted">Total Guests</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="dashboard-icon bg-success bg-opacity-10 text-success me-3">
                                        <i class="bi bi-person-plus"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?php echo $stats['new_guests']; ?></h4>
                                        <small class="text-muted">New (Last 30 Days)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="dashboard-icon bg-warning bg-opacity-10 text-warning me-3">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?php echo $stats['active_bookings']; ?></h4>
                                        <small class="text-muted">Active Bookings</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guests Table -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Total Bookings</th>
                                        <th>Total Spent</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($users->num_rows > 0): ?>
                                        <?php while($user = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-2">
                                                        <i class="bi bi-person-fill"></i>
                                                    </div>
                                                    <strong><?php echo $user['name']; ?></strong>
                                                </div>
                                            </td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td><?php echo $user['phone'] ?: '<span class="text-muted">N/A</span>'; ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $user['total_bookings']; ?> booking(s)
                                                </span>
                                            </td>
                                            <td class="fw-bold text-success">
                                                <?php echo formatCurrency($user['total_spent']); ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" 
                                                        onclick="viewGuest(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No guests found</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Guest Modal -->
    <div class="modal fade" id="viewGuestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Guest Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Personal Information</h6>
                            <p class="mb-2"><strong>Name:</strong> <span id="view_name"></span></p>
                            <p class="mb-2"><strong>Email:</strong> <span id="view_email"></span></p>
                            <p class="mb-2"><strong>Phone:</strong> <span id="view_phone"></span></p>
                            <p class="mb-2"><strong>Member Since:</strong> <span id="view_created"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Booking Statistics</h6>
                            <p class="mb-2"><strong>Total Bookings:</strong> <span id="view_bookings" class="badge bg-primary"></span></p>
                            <p class="mb-2"><strong>Total Spent:</strong> <span id="view_spent" class="text-success fw-bold"></span></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="fw-bold mb-3">Booking History</h6>
                    <div id="booking_history">
                        <div class="text-center text-muted">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading bookings...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewGuest(user) {
            document.getElementById('view_name').textContent = user.name;
            document.getElementById('view_email').textContent = user.email;
            document.getElementById('view_phone').textContent = user.phone || 'N/A';
            document.getElementById('view_created').textContent = new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('view_bookings').textContent = user.total_bookings;
            document.getElementById('view_spent').textContent = '₱' + parseFloat(user.total_spent).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
            // Load booking history via AJAX
            fetch('get-user-bookings.php?user_id=' + user.id)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.length > 0) {
                        html = '<div class="table-responsive"><table class="table table-sm">';
                        html += '<thead><tr><th>Booking ID</th><th>Room</th><th>Check-in</th><th>Status</th><th>Total</th></tr></thead><tbody>';
                        data.forEach(booking => {
                            html += '<tr>';
                            html += '<td>#' + booking.id + '</td>';
                            html += '<td>' + booking.room_type + ' - ' + booking.room_number + '</td>';
                            html += '<td>' + booking.check_in + '</td>';
                            html += '<td><span class="status-badge status-' + booking.status + '">' + booking.status + '</span></td>';
                            html += '<td>₱' + parseFloat(booking.total_price).toFixed(2) + '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody></table></div>';
                    } else {
                        html = '<p class="text-muted text-center">No bookings yet</p>';
                    }
                    document.getElementById('booking_history').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('booking_history').innerHTML = '<p class="text-danger text-center">Failed to load bookings</p>';
                });
            
            var viewModal = new bootstrap.Modal(document.getElementById('viewGuestModal'));
            viewModal.show();
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>