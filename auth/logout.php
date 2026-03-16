<?php
require_once __DIR__ . '/../includes/header.php';

if (isset($_SESSION['user'])) {
    destroySession();
    $loggedin = false;
?>
    <div class="text-center py-5">
        <i class="bi bi-check-circle text-success" style="font-size:4rem"></i>
        <h3 class="mt-3 fw-bold">You've been logged out</h3>
        <p class="text-muted">Thanks for visiting FastMessenger!</p>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary mt-2">
            <i class="bi bi-house-door"></i> Return Home
        </a>
    </div>
<?php } else { ?>
    <div class="text-center py-5">
        <i class="bi bi-exclamation-circle text-warning" style="font-size:4rem"></i>
        <h3 class="mt-3 fw-bold">You're not logged in</h3>
        <p class="text-muted">There's nothing to log out from.</p>
        <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary mt-2">
            <i class="bi bi-box-arrow-in-right"></i> Log In
        </a>
    </div>
<?php } ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
