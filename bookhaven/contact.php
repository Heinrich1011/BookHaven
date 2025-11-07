<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - BookHaven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Contact Us</h1>
            <p class="lead">We're here to help you</p>
        </div>
    </section>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <h3 class="fw-bold mb-4">Get In Touch</h3>
                <div class="mb-4">
                    <h5><i class="bi bi-geo-alt-fill text-primary me-2"></i>Address</h5>
                    <p>123 Hotel Di Mahanap-hanap Street, Gonzaga<br>Cagayan, Philippines</p>
                </div>
                <div class="mb-4">
                    <h5><i class="bi bi-telephone-fill text-primary me-2"></i>Phone</h5>
                    <p>+63 123 456 7890<br>+63 987 654 3210</p>
                </div>
                <div class="mb-4">
                    <h5><i class="bi bi-envelope-fill text-primary me-2"></i>Email</h5>
                    <p>info@bookhaven.com<br>reservations@bookhaven.com</p>
                </div>
                <div class="mb-4">
                    <h5><i class="bi bi-clock-fill text-primary me-2"></i>Business Hours</h5>
                    <p>Front Desk: 24/7<br>Support: 8:00 AM - 10:00 PM</p>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-4">Send Us a Message</h3>
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>