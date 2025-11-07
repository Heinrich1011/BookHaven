<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$conn = getDBConnection();
$message = '';

// Handle status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'confirm') {
        $update = "UPDATE bookings SET status = 'confirmed' WHERE id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            // Update room status
            $conn->query("UPDATE rooms r 
                         INNER JOIN bookings b ON r.id = b.room_id 
                         SET r.status = 'occupied' 
                         WHERE b.id = $booking_id");
            $message = '<div class="alert alert-success">Booking confirmed successfully!</div>';
        }
    } elseif ($action === 'cancel') {
        $update = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Booking cancelled successfully!</div>';
        }
    } elseif ($action === 'complete') {
        $update = "UPDATE bookings SET status = 'completed' WHERE id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            // Update room status back to available
            $conn->query("UPDATE rooms r 
                         INNER JOIN bookings b ON r.id = b.room_id 
                         SET r.status = 'available' 
                         WHERE b.id = $booking_id");
            $message = '<div class="alert alert-success">Booking marked as completed!</div>';
        }
    }
}

// Filter parameters
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$query = "SELECT b.*, u.name as guest_name, u.email as guest_email, u.phone as guest_phone,
          r.room_type, r.room_number, r.image 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN rooms r ON b.room_id = r.id 
          WHERE 1=1";

if ($status_filter) {
    $query .= " AND b.status = '$status_filter'";
}

if ($search) {
    $query .= " AND (u.name LIKE '%$search%' OR r.room_number LIKE '%$search%' OR b.id LIKE '%$search%')";
}

$query .= " ORDER BY b.created_at DESC";

$bookings = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - BookHaven Admin</title>
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
                            <a class="nav-link active" href="manage-bookings.php">
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
                <h2 class="fw-bold mb-4">Manage Bookings</h2>

                <?php echo $message; ?>

                <!-- Filters -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Filter by Status</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by guest name, room number, or booking ID..."
                                       value="<?php echo $search; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Search
                                </button>
                            </div>
                        </form>
                        <?php if ($status_filter || $search): ?>
                        <div class="mt-2">
                            <a href="manage-bookings.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Clear Filters
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Days</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($bookings->num_rows > 0): ?>
                                        <?php while($booking = $bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                            <td>
                                                <div>
                                                    <i class="bi bi-person-circle me-1"></i>
                                                    <strong><?php echo $booking['guest_name']; ?></strong>
                                                </div>
                                                <small class="text-muted"><?php echo $booking['guest_email']; ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $booking['room_type']; ?></span>
                                                <br><small><?php echo $booking['room_number']; ?></small>
                                            </td>
                                            <td><?php echo formatDate($booking['check_in']); ?></td>
                                            <td><?php echo formatDate($booking['check_out']); ?></td>
                                            <td><?php echo calculateDays($booking['check_in'], $booking['check_out']); ?> nights</td>
                                            <td class="fw-bold text-primary"><?php echo formatCurrency($booking['total_price']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if ($booking['status'] === 'pending'): ?>
                                                        <a href="?action=confirm&id=<?php echo $booking['id']; ?>" 
                                                           class="btn btn-sm btn-success" 
                                                           onclick="return confirm('Confirm this booking?')"
                                                           title="Confirm">
                                                            <i class="bi bi-check-lg"></i>
                                                        </a>
                                                        <a href="?action=cancel&id=<?php echo $booking['id']; ?>" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Cancel this booking?')"
                                                           title="Cancel">
                                                            <i class="bi bi-x-lg"></i>
                                                        </a>
                                                    <?php elseif ($booking['status'] === 'confirmed'): ?>
                                                        <a href="?action=complete&id=<?php echo $booking['id']; ?>" 
                                                           class="btn btn-sm btn-info"
                                                           onclick="return confirm('Mark as completed?')"
                                                           title="Complete">
                                                            <i class="bi bi-check-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-primary" 
                                                            onclick="viewBooking(<?php echo htmlspecialchars(json_encode($booking)); ?>)"
                                                            title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No bookings found</p>
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

    <!-- View Booking Modal -->
    <div class="modal fade" id="viewBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img id="view_room_image" src="" class="img-fluid rounded mb-3" alt="Room">
                        </div>
                        <div class="col-md-8">
                            <h5 id="view_booking_id" class="mb-3"></h5>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">Guest Information</h6>
                                <p class="mb-1"><strong>Name:</strong> <span id="view_guest_name"></span></p>
                                <p class="mb-1"><strong>Email:</strong> <span id="view_guest_email"></span></p>
                                <p class="mb-1"><strong>Phone:</strong> <span id="view_guest_phone"></span></p>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">Room Information</h6>
                                <p class="mb-1"><strong>Type:</strong> <span id="view_room_type"></span></p>
                                <p class="mb-1"><strong>Number:</strong> <span id="view_room_number"></span></p>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">Booking Details</h6>
                                <p class="mb-1"><strong>Check-in:</strong> <span id="view_check_in"></span></p>
                                <p class="mb-1"><strong>Check-out:</strong> <span id="view_check_out"></span></p>
                                <p class="mb-1"><strong>Total:</strong> <span id="view_total" class="text-primary fw-bold"></span></p>
                                <p class="mb-1"><strong>Status:</strong> <span id="view_status"></span></p>
                            </div>
                            
                            <div id="special_requests_section" class="mb-3" style="display: none;">
                                <h6 class="fw-bold">Special Requests</h6>
                                <p id="view_special_requests" class="text-muted"></p>
                            </div>
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
        function viewBooking(booking) {
            document.getElementById('view_room_image').src = '../' + booking.image;
            document.getElementById('view_booking_id').textContent = 'Booking #' + booking.id;
            document.getElementById('view_guest_name').textContent = booking.guest_name;
            document.getElementById('view_guest_email').textContent = booking.guest_email;
            document.getElementById('view_guest_phone').textContent = booking.guest_phone || 'N/A';
            document.getElementById('view_room_type').textContent = booking.room_type;
            document.getElementById('view_room_number').textContent = booking.room_number;
            document.getElementById('view_check_in').textContent = new Date(booking.check_in).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('view_check_out').textContent = new Date(booking.check_out).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('view_total').textContent = '₱' + parseFloat(booking.total_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
            let statusBadge = '<span class="status-badge status-' + booking.status + '">' + booking.status.charAt(0).toUpperCase() + booking.status.slice(1) + '</span>';
            document.getElementById('view_status').innerHTML = statusBadge;
            
            if (booking.special_requests) {
                document.getElementById('view_special_requests').textContent = booking.special_requests;
                document.getElementById('special_requests_section').style.display = 'block';
            } else {
                document.getElementById('special_requests_section').style.display = 'none';
            }
            
            var viewModal = new bootstrap.Modal(document.getElementById('viewBookingModal'));
            viewModal.show();
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>