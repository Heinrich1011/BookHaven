<?php
session_start();
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$conn = getDBConnection();
$message = '';

// Display session message if exists
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Handle Add/Edit/Delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $room_type = sanitize($_POST['room_type']);
            $room_number = sanitize($_POST['room_number']);
            $description = sanitize($_POST['description']);
            $price = (float)$_POST['price'];
            $capacity = (int)$_POST['capacity'];
            $status = sanitize($_POST['status']);
            $amenities = sanitize($_POST['amenities']);
            // Handle JPEG image upload (optional)
            $upload_dir = __DIR__ . '/../assets/images/rooms/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $image = 'assets/images/rooms/' . strtolower($room_type) . '.jpg'; // default fallback
            if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['room_image'];
                $maxSize = 10 * 1024 * 1024; // 2MB

                // Basic checks
                if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= $maxSize) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                    $allowedMimes = ['image/jpeg'];
                    $allowedExts = ['jpg', 'jpeg'];

                    if (in_array($mime, $allowedMimes) && in_array($ext, $allowedExts)) {
                        // Generate a safe unique filename
                        $newName = time() . '_' . bin2hex(random_bytes(6)) . '.jpg';
                        $target = $upload_dir . $newName;

                        if (move_uploaded_file($file['tmp_name'], $target)) {
                            // Use web-relative path
                            $image = 'assets/images/rooms/' . $newName;
                        } else {
                            $_SESSION['message'] = '<div class="alert alert-warning">Image upload failed, using default image.</div>';
                        }
                    } else {
                        $_SESSION['message'] = '<div class="alert alert-danger">Invalid image format. Only JPEG (.jpg/.jpeg) is allowed.</div>';
                    }
                } else {
                    $_SESSION['message'] = '<div class="alert alert-danger">Image upload error or file too large (max 2MB).</div>';
                }
            }

            $insert = "INSERT INTO rooms (room_type, room_number, description, price, capacity, status, image, amenities) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert);
            // FIXED: Changed from "ssdissss" to "sssdisss" - description is now a string (s) not integer (i)
            $stmt->bind_param("sssdisss", $room_type, $room_number, $description, $price, $capacity, $status, $image, $amenities);

            if ($stmt->execute()) {
                $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Room added successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Failed to add room.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            }
            
            // Redirect to prevent form resubmission
            header("Location: manage-rooms.php");
            exit();
        }
        
        if ($action === 'edit') {
            $room_id = (int)$_POST['room_id'];
            $room_type = sanitize($_POST['room_type']);
            $room_number = sanitize($_POST['room_number']);
            $description = sanitize($_POST['description']);
            $price = (float)$_POST['price'];
            $capacity = (int)$_POST['capacity'];
            $status = sanitize($_POST['status']);
            $amenities = sanitize($_POST['amenities']);
            // Handle optional JPEG image upload for edit
            $upload_dir = __DIR__ . '/../assets/images/rooms/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $image = null;
            if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['room_image'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= $maxSize) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                    $allowedMimes = ['image/jpeg'];
                    $allowedExts = ['jpg', 'jpeg'];

                    if (in_array($mime, $allowedMimes) && in_array($ext, $allowedExts)) {
                        $newName = time() . '_' . bin2hex(random_bytes(6)) . '.jpg';
                        $target = $upload_dir . $newName;

                        if (move_uploaded_file($file['tmp_name'], $target)) {
                            $image = 'assets/images/rooms/' . $newName;
                        } else {
                            $_SESSION['message'] = '<div class="alert alert-warning">Image upload failed, existing image kept.</div>';
                        }
                    } else {
                        $_SESSION['message'] = '<div class="alert alert-danger">Invalid image format. Only JPEG (.jpg/.jpeg) is allowed.</div>';
                    }
                } else {
                    $_SESSION['message'] = '<div class="alert alert-danger">Image upload error or file too large (max 2MB).</div>';
                }
            }

            if ($image !== null) {
                $update = "UPDATE rooms SET room_type = ?, room_number = ?, description = ?, price = ?, 
                          capacity = ?, status = ?, amenities = ?, image = ? WHERE id = ?";
                $stmt = $conn->prepare($update);
                // FIXED: Changed from "ssdissssi" to "sssdisssi"
                $stmt->bind_param("sssdisssi", $room_type, $room_number, $description, $price, $capacity, $status, $amenities, $image, $room_id);
            } else {
                $update = "UPDATE rooms SET room_type = ?, room_number = ?, description = ?, price = ?, 
                          capacity = ?, status = ?, amenities = ? WHERE id = ?";
                $stmt = $conn->prepare($update);
                // FIXED: Changed from "ssdisssi" to "sssdissi"
                $stmt->bind_param("sssdissi", $room_type, $room_number, $description, $price, $capacity, $status, $amenities, $room_id);
            }

            if ($stmt->execute()) {
                $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Room updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Failed to update room.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            }
            
            // Redirect to prevent form resubmission
            header("Location: manage-rooms.php");
            exit();
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $room_id = (int)$_GET['delete'];
    $delete = "DELETE FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($delete);
    $stmt->bind_param("i", $room_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> Room deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Cannot delete room. It may have existing bookings.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    
    // Redirect to prevent resubmission
    header("Location: manage-rooms.php");
    exit();
}

// Get all rooms
$rooms = $conn->query("SELECT * FROM rooms ORDER BY room_type, room_number");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - BookHaven Admin</title>
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
                            <a class="nav-link active" href="manage-rooms.php">
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
                    <h2 class="fw-bold">Manage Rooms</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="bi bi-plus-circle me-2"></i>Add New Room
                    </button>
                </div>

                <?php echo $message; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Room Number</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Capacity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($room = $rooms->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $room['id']; ?></td>
                                        <td><strong><?php echo $room['room_number']; ?></strong></td>
                                        <td><span class="badge bg-primary"><?php echo $room['room_type']; ?></span></td>
                                        <td><?php echo substr($room['description'], 0, 50); ?>...</td>
                                        <td class="fw-bold"><?php echo formatCurrency($room['price']); ?></td>
                                        <td><?php echo $room['capacity']; ?> guests</td>
                                        <td>
                                            <?php if ($room['status'] === 'available'): ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php elseif ($room['status'] === 'occupied'): ?>
                                                <span class="badge bg-danger">Occupied</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Maintenance</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="editRoom(<?php echo htmlspecialchars(json_encode($room)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="?delete=<?php echo $room['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this room?')">
                                                <i class="bi bi-trash"></i>
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

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room Type</label>
                                <select name="room_type" class="form-select" required>
                                    <option value="Single">Single</option>
                                    <option value="Double">Double</option>
                                    <option value="Deluxe">Deluxe</option>
                                    <option value="Family">Family</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room Number</label>
                                <input type="text" name="room_number" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price (per night)</label>
                                <input type="number" name="price" class="form-control" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Capacity</label>
                                <input type="number" name="capacity" class="form-control" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="available">Available</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amenities</label>
                            <input type="text" name="amenities" class="form-control" 
                                   placeholder="Free WiFi, Air Conditioning, TV, Mini Fridge" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Image (JPEG only, optional)</label>
                            <input type="file" name="room_image" accept="image/jpeg" class="form-control">
                            <div class="form-text">Accepted: .jpg</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editRoomForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="room_id" id="edit_room_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room Type</label>
                                <select name="room_type" id="edit_room_type" class="form-select" required>
                                    <option value="Single">Single</option>
                                    <option value="Double">Double</option>
                                    <option value="Deluxe">Deluxe</option>
                                    <option value="Family">Family</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room Number</label>
                                <input type="text" name="room_number" id="edit_room_number" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price (per night)</label>
                                <input type="number" name="price" id="edit_price" class="form-control" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Capacity</label>
                                <input type="number" name="capacity" id="edit_capacity" class="form-control" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="available">Available</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amenities</label>
                            <input type="text" name="amenities" id="edit_amenities" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Image (JPEG only, optional)</label>
                            <input type="file" name="room_image" accept="image/jpeg" class="form-control">
                            <div class="form-text">Uploading a new image will replace the existing one. Max size 2MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editRoom(room) {
            document.getElementById('edit_room_id').value = room.id;
            document.getElementById('edit_room_type').value = room.room_type;
            document.getElementById('edit_room_number').value = room.room_number;
            document.getElementById('edit_description').value = room.description;
            document.getElementById('edit_price').value = room.price;
            document.getElementById('edit_capacity').value = room.capacity;
            document.getElementById('edit_status').value = room.status;
            document.getElementById('edit_amenities').value = room.amenities;
            
            var editModal = new bootstrap.Modal(document.getElementById('editRoomModal'));
            editModal.show();
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>