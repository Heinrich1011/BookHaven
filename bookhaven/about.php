<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - BookHaven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">About BookHaven</h1>
            <p class="lead">Your trusted partner in hospitality</p>
        </div>
    </section>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <h2 class="fw-bold mb-4">Our Story</h2>
                <p>BookHaven was established with a vision to provide comfortable, affordable, and memorable stays for travelers from all walks of life. Located in the heart of the city, we combine modern amenities with warm Filipino hospitality.</p>
                <p>With years of experience in the hospitality industry, we understand what makes a perfect stay. Our dedicated team works tirelessly to ensure every guest feels at home.</p>
            </div>
            <div class="col-lg-6 mb-4">
                <h2 class="fw-bold mb-4">Why Choose Us?</h2>
                <ul class="list-unstyled">
                    <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Prime Location:</strong> Easy access to major attractions</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Modern Facilities:</strong> Well-equipped rooms with all amenities</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>24/7 Service:</strong> Round-the-clock assistance</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Competitive Rates:</strong> Best value for your money</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Easy Booking:</strong> Simple online reservation system</li>
                </ul>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>