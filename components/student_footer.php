<style>
    html, body {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.content {
    flex: 1;
}

</style>
<footer class="bg-success text-light mt-auto py-4" id="student_footer">
    <div class="container">
        <div class="row">
            <!-- About Section -->
            <div class="col-md-4">
                <h5>About EduMart</h5>
                <p>EduMart is a student-driven marketplace where you can buy and sell educational materials, gadgets, and more.</p>
            </div>

            <!-- Contact Information -->
            <div class="col-md-4">
                <h5>Contact Us</h5>
                <p><strong>Address:</strong> 123 EduMart Street, City, Country</p>
                <p><strong>Phone:</strong> +123 456 7890</p>
                <p><strong>Email:</strong> support@edumart.com</p>
            </div>

            <!-- Quick Links & Terms -->
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="terms.php" class="text-light text-decoration-none">Terms & Conditions</a></li>
                    <li><a href="privacy.php" class="text-light text-decoration-none">Privacy Policy</a></li>
                </ul>

                <!-- Small Contact Form -->
                <h5>Get in Touch</h5>
                <form action="/send_message.php" method="post">
                    <div class="mb-2">
                        <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                    </div>
                    <div class="mb-2">
                        <textarea name="message" class="form-control" rows="2" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Send</button>
                </form>
            </div>
        </div>

        <!-- Copyright -->
        <div class="text-center mt-3">
            <p class="mb-0">&copy; <?= date("Y") ?> EduMart. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS (Optional, if needed) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
