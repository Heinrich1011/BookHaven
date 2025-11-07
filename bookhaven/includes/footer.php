<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-buildings"></i> BookHaven
                </h5>
                <p class="text-white-50">Your perfect stay awaits. Experience comfort, luxury, and exceptional service at BookHaven Hotel.</p>
                <div class="social-links mt-3">
                    <a href="https://www.facebook.com/" class="me-3"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="https://www.twitter.com" class="me-3"><i class="bi bi-twitter fs-5"></i></a>
                    <a href="https://www.instagram.com" class="me-3"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="https://www.linkedin.com"><i class="bi bi-linkedin fs-5"></i></a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>rooms.php">Rooms</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>about.php">About Us</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>contact.php">Contact</a></li>
                    <?php if (!isLoggedIn()): ?>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>register.php">Register</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3">Contact Info</h5>
                <ul class="list-unstyled text-white-50">
                    <li class="mb-2">
                        <i class="bi bi-geo-alt-fill me-2"></i>
                        123 Hotel Di Mahanap-hanap Street, Gonzaga Cagayan, Philippines
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-telephone-fill me-2"></i>
                        +63 9565780262
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-envelope-fill me-2"></i>
                        info@bookhaven.com
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-clock-fill me-2"></i>
                        24/7 Support Available
                    </li>
                </ul>
            </div>
        </div>
        <hr class="bg-white opacity-25">
        <div class="row">
            <div class="col-12 text-center">
                <p class="text-white-50 mb-0">
                    &copy; <?php echo date('Y'); ?> BookHaven Hotel. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>