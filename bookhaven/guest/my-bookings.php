<?php
require_once '../config.php';
if (!isLoggedIn() || !isGuest()) redirect('login.php');

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle cancellation
if (isset($_GET['cancel'])) {
    $booking_id = (int)$_GET['cancel'];
    $update = "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    header("Location: my-bookings.php?msg=cancelled");
    exit();
}

$bookings = $conn->query("SELECT b.*, r.room_type, r.room_number, r.image 
                          FROM bookings b 
                          JOIN rooms r ON b.room_id = r.id 
                          WHERE b.user_id = $user_id 
                          ORDER BY b.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - BookHaven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printReceipt, #printReceipt * {
                visibility: visible;
            }
            #printReceipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="fw-bold mb-4">My Bookings</h2>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'cancelled'): ?>
        <div class="alert alert-success">Booking cancelled successfully!</div>
        <?php endif; ?>

        <?php if ($bookings->num_rows > 0): ?>
            <div class="row">
                <?php while($booking = $bookings->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="fw-bold mb-0">Booking #<?php echo $booking['id']; ?></h5>
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <img src="../<?php echo $booking['image']; ?>" class="img-fluid rounded" alt="Room">
                            </div>
                            
                            <p><strong>Room:</strong> <?php echo $booking['room_type']; ?> - <?php echo $booking['room_number']; ?></p>
                            <p><strong>Check-in:</strong> <?php echo formatDate($booking['check_in']); ?></p>
                            <p><strong>Check-out:</strong> <?php echo formatDate($booking['check_out']); ?></p>
                            <p><strong>Total:</strong> <span class="text-primary fw-bold"><?php echo formatCurrency($booking['total_price']); ?></span></p>
                            
                            <div class="d-flex gap-2">
                                <?php if ($booking['status'] == 'pending'): ?>
                                <a href="?cancel=<?php echo $booking['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to cancel this booking?')">
                                    <i class="bi bi-x-circle me-1"></i>Cancel Booking
                                </a>
                                <?php endif; ?>
                                
                                <button onclick="printReceipt(<?php echo htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8'); ?>)" 
                                        class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-printer me-1"></i>Print Receipt
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No bookings found. <a href="../rooms.php" class="alert-link">Book a room now!</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Hidden Receipt Template -->
    <div id="printReceipt" style="display: none;">
        <div style="max-width: 800px; margin: 0 auto; padding: 40px; font-family: Arial, sans-serif;">
            <div style="text-align: center; border-bottom: 3px solid #333; padding-bottom: 20px; margin-bottom: 30px;">
                <h1 style="margin: 0; color: #333; font-size: 36px;">BookHaven</h1>
                <p style="margin: 5px 0; color: #666; font-size: 14px;">Luxury Hotel & Resort</p>
            </div>
            
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="color: #333; margin: 0;">BOOKING RECEIPT</h2>
                <p style="color: #666; margin: 5px 0;" id="receiptDate"></p>
            </div>
            
            <div style="background-color: #f8f9fa; padding: 20px; margin-bottom: 30px; border-radius: 5px;">
                <h3 style="color: #333; margin-top: 0;">Booking Details</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666; width: 40%;"><strong>Booking ID:</strong></td>
                        <td style="padding: 8px 0; color: #333;" id="receiptBookingId"></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;"><strong>Guest Name:</strong></td>
                        <td style="padding: 8px 0; color: #333;" id="receiptGuestName"></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;"><strong>Status:</strong></td>
                        <td style="padding: 8px 0; color: #333;" id="receiptStatus"></td>
                    </tr>
                </table>
            </div>
            
            <div style="background-color: #f8f9fa; padding: 20px; margin-bottom: 30px; border-radius: 5px;">
                <h3 style="color: #333; margin-top: 0;">Room Information</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666; width: 40%;"><strong>Room Type:</strong></td>
                        <td style="padding: 8px 0; color: #333;" id="receiptRoomType"></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;"><strong>Room Number:</strong></td>
                        <td style="padding: 8px 0; color: #333;" id="receiptRoomNumber"></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;"><strong>Check-in Date:</strong></td>
                        <td style="padding: 8px 0; color: #333;" id="receiptCheckIn"></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;"><strong>Check-out Date:</strong></td>
                        <td style="padding: 8px 0; color: #333;" id="receiptCheckOut"></td>
                    </tr>
                </table>
            </div>
            
            <div style="border-top: 2px solid #333; padding-top: 20px; margin-top: 30px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 10px 0; font-size: 20px;"><strong>Total Amount:</strong></td>
                        <td style="text-align: right; font-size: 24px; color: #0d6efd; font-weight: bold;" id="receiptTotal"></td>
                    </tr>
                </table>
            </div>
            
            <div style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 12px;">
                <p>Thank you for choosing BookHaven! Please present this receipt at check-in.</p>
                <p>For inquiries, please contact us at: info@bookhaven.com | +63 9565780262</p>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function printReceipt(booking) {
            // Get guest name from session/page
            const guestName = "<?php echo $_SESSION['username'] ?? 'Guest'; ?>";
            
            // Populate receipt data
            document.getElementById('receiptDate').textContent = new Date().toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            document.getElementById('receiptBookingId').textContent = '#' + booking.id;
            document.getElementById('receiptGuestName').textContent = guestName;
            document.getElementById('receiptStatus').textContent = booking.status.charAt(0).toUpperCase() + booking.status.slice(1);
            document.getElementById('receiptRoomType').textContent = booking.room_type;
            document.getElementById('receiptRoomNumber').textContent = booking.room_number;
            document.getElementById('receiptCheckIn').textContent = formatDate(booking.check_in);
            document.getElementById('receiptCheckOut').textContent = formatDate(booking.check_out);
            document.getElementById('receiptTotal').textContent = '₱' + parseFloat(booking.total_price).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            // Show and print
            document.getElementById('printReceipt').style.display = 'block';
            window.print();
            document.getElementById('printReceipt').style.display = 'none';
        }
        
        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>