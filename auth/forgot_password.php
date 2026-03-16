<?php
require_once __DIR__ . '/../includes/header.php';
$error = $success = $user_input = '';

if (isset($_POST['user'])) {
    $user_input = sanitizeString($_POST['user']);

    if ($user_input === '') {
        $error = 'Please enter your username.';
    } else {
        $stmt = queryMysql("SELECT user FROM members WHERE user=?", [$user_input]);
        $row = $stmt->fetch();

        if ($row) {
            queryMysql("DELETE FROM password_resets WHERE user=?", [$user_input]);

            $token   = bin2hex(random_bytes(32));
            $expires = time() + 3600;
            queryMysql("INSERT INTO password_resets (user, token, expires) VALUES(?, ?, ?)",
                [$user_input, $token, $expires]);

            $resetLink = BASE_URL . "/auth/reset_password.php?token=$token";
            $success   = $resetLink;
        } else {
            $error = 'No account found with that username.';
        }
    }
}
?>
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-key text-primary" style="font-size:2.5rem"></i>
                    <h3 class="mt-2 fw-bold">Forgot Password</h3>
                    <p class="text-muted">Enter your username to reset your password</p>
                </div>
<?php if ($error): ?>
                <div class="alert alert-danger py-2">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
<?php endif; ?>
<?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <strong>Reset link generated!</strong><br>
                    <a href="<?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>"
                       class="btn btn-success btn-sm mt-2">
                        <i class="bi bi-arrow-right"></i> Click here to reset your password
                    </a>
                    <p class="small text-muted mt-2 mb-0">This link expires in 1 hour.</p>
                </div>
<?php else: ?>
                <form method="post" action="<?= BASE_URL ?>/auth/forgot_password.php?r=<?= $randstr ?>">
                    <div class="mb-3">
                        <label for="user" class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" id="user" name="user"
                               maxlength="16" value="<?= htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8') ?>" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-key"></i> Get Reset Link
                    </button>
                </form>
<?php endif; ?>
                <p class="text-center mt-3 mb-0 small">
                    Remember your password?
                    <a href="<?= BASE_URL ?>/auth/login.php?r=<?= $randstr ?>" class="fw-semibold">Log In</a>
                </p>
            </div>
        </div>
    </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
