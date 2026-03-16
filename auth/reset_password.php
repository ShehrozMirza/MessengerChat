<?php
require_once __DIR__ . '/../includes/header.php';
$error = $success = '';
$token = $_GET['token'] ?? '';
$validToken = false;
$tokenUser  = '';

if ($token !== '') {
    $stmt = queryMysql("SELECT user, expires FROM password_resets WHERE token=?", [$token]);
    $row  = $stmt->fetch();

    if ($row && $row['expires'] > time()) {
        $validToken = true;
        $tokenUser  = $row['user'];
    }
}

if (isset($_POST['pass']) && isset($_POST['token'])) {
    $token     = $_POST['token'];
    $pass_new  = $_POST['pass'] ?? '';
    $pass_conf = $_POST['pass_confirm'] ?? '';

    $stmt = queryMysql("SELECT user, expires FROM password_resets WHERE token=?", [$token]);
    $row  = $stmt->fetch();

    if (!$row || $row['expires'] <= time()) {
        $error = 'This reset link has expired. Please request a new one.';
    } elseif ($pass_new === '') {
        $error      = 'Please enter a new password.';
        $validToken = true;
        $tokenUser  = $row['user'];
    } elseif ($pass_new !== $pass_conf) {
        $error      = 'Passwords do not match.';
        $validToken = true;
        $tokenUser  = $row['user'];
    } else {
        $hashed = password_hash($pass_new, PASSWORD_DEFAULT);
        queryMysql("UPDATE members SET pass=? WHERE user=?", [$hashed, $row['user']]);
        queryMysql("DELETE FROM password_resets WHERE user=?", [$row['user']]);
        $success = true;
    }
}
?>
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4 p-md-5">
<?php if ($success): ?>
                <div class="text-center py-3">
                    <i class="bi bi-check-circle text-success" style="font-size:3.5rem"></i>
                    <h3 class="mt-3 fw-bold">Password Reset!</h3>
                    <p class="text-muted">Your password has been changed successfully.</p>
                    <a href="<?= BASE_URL ?>/auth/login.php?r=<?= $randstr ?>" class="btn btn-primary mt-2">
                        <i class="bi bi-box-arrow-in-right"></i> Log In Now
                    </a>
                </div>
<?php elseif ($validToken): ?>
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock text-primary" style="font-size:2.5rem"></i>
                    <h3 class="mt-2 fw-bold">Reset Password</h3>
                    <p class="text-muted">
                        Set a new password for
                        <strong><?= htmlspecialchars($tokenUser, ENT_QUOTES, 'UTF-8') ?></strong>
                    </p>
                </div>
<?php if ($error): ?>
                <div class="alert alert-danger py-2">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
<?php endif; ?>
                <form method="post" action="<?= BASE_URL ?>/auth/reset_password.php">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-3">
                        <label for="pass" class="form-label fw-semibold">New Password</label>
                        <input type="password" class="form-control" id="pass" name="pass"
                               maxlength="255" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="pass_confirm" class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" class="form-control" id="pass_confirm" name="pass_confirm"
                               maxlength="255" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-check-lg"></i> Set New Password
                    </button>
                </form>
<?php else: ?>
                <div class="text-center py-3">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size:3.5rem"></i>
                    <h3 class="mt-3 fw-bold">Invalid or Expired Link</h3>
                    <p class="text-muted">
                        <?= $error ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : 'This password reset link is invalid or has expired.' ?>
                    </p>
                    <a href="<?= BASE_URL ?>/auth/forgot_password.php?r=<?= $randstr ?>" class="btn btn-primary mt-2">
                        <i class="bi bi-arrow-repeat"></i> Request New Link
                    </a>
                </div>
<?php endif; ?>
            </div>
        </div>
    </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
