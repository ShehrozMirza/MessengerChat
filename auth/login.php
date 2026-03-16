<?php
require_once __DIR__ . '/../includes/header.php';
$error = $user_input = '';

if (isset($_POST['user'])) {
    $user_input = sanitizeString($_POST['user']);
    $pass_input = $_POST['pass'] ?? '';

    if ($user_input === '' || $pass_input === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = queryMysql("SELECT user, pass FROM members WHERE user=?", [$user_input]);
        $row  = $stmt->fetch();

        if ($row) {
            $valid = false;

            if (password_verify($pass_input, $row['pass'])) {
                $valid = true;
            } elseif ($row['pass'] === $pass_input) {
                $valid  = true;
                $hashed = password_hash($pass_input, PASSWORD_DEFAULT);
                queryMysql("UPDATE members SET pass=? WHERE user=?", [$hashed, $row['user']]);
            }

            if ($valid) {
                $_SESSION['user'] = $row['user'];
                header("Location: " . BASE_URL . "/index.php?r=$randstr");
                exit;
            }
        }

        $error = 'Invalid username or password.';
    }
}
?>
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-box-arrow-in-right text-primary" style="font-size:2.5rem"></i>
                    <h3 class="mt-2 fw-bold">Log In</h3>
                    <p class="text-muted">Enter your credentials to continue</p>
                </div>
<?php if ($error): ?>
                <div class="alert alert-danger py-2">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
<?php endif; ?>
                <form method="post" action="<?= BASE_URL ?>/auth/login.php?r=<?= $randstr ?>">
                    <div class="mb-3">
                        <label for="user" class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" id="user" name="user"
                               maxlength="16" value="<?= htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8') ?>" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="pass" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="pass" name="pass"
                               maxlength="255" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right"></i> Log In
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>/auth/forgot_password.php?r=<?= $randstr ?>" class="small text-muted">
                        <i class="bi bi-key"></i> Forgot your password?
                    </a>
                </div>
                <p class="text-center mt-2 mb-0 small">
                    Don't have an account?
                    <a href="<?= BASE_URL ?>/auth/signup.php?r=<?= $randstr ?>" class="fw-semibold">Sign Up</a>
                </p>
            </div>
        </div>
    </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
