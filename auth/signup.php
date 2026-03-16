<?php
require_once __DIR__ . '/../includes/header.php';
$error = $user_input = '';

if (isset($_SESSION['user'])) destroySession();

if (isset($_POST['user'])) {
    $user_input = sanitizeString($_POST['user']);
    $pass_input = $_POST['pass'] ?? '';

    if ($user_input === '' || $pass_input === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = queryMysql("SELECT user FROM members WHERE user=?", [$user_input]);

        if ($stmt->rowCount()) {
            $error = 'That username is already taken.';
        } else {
            $hashed = password_hash($pass_input, PASSWORD_DEFAULT);
            queryMysql("INSERT INTO members (user, pass) VALUES(?, ?)",
                [$user_input, $hashed]);
            $_SESSION['user'] = $user_input;
            $signupSuccess = true;
        }
    }
}
?>
    <div class="auth-card">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus text-primary" style="font-size:2.5rem"></i>
                    <h3 class="mt-2 fw-bold">Create Account</h3>
                    <p class="text-muted">Join FastMessenger today</p>
                </div>
<?php if ($error): ?>
                <div class="alert alert-danger py-2">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
<?php endif; ?>
                <form method="post" action="<?= BASE_URL ?>/auth/signup.php?r=<?= $randstr ?>">
                    <div class="mb-3">
                        <label for="user" class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" id="user" name="user"
                               maxlength="16" value="<?= htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8') ?>"
                               oninput="checkUser(this.value)" required autofocus>
                        <div id="used" class="form-text mt-1"></div>
                    </div>
                    <div class="mb-4">
                        <label for="pass" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="pass" name="pass"
                               maxlength="255" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-person-plus"></i> Sign Up
                    </button>
                </form>
                <p class="text-center mt-3 mb-0 small">
                    Already have an account?
                    <a href="<?= BASE_URL ?>/auth/login.php?r=<?= $randstr ?>" class="fw-semibold">Log In</a>
                </p>
            </div>
        </div>
    </div>
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border:none;border-radius:14px">
                <div class="modal-body text-center p-4">
                    <div style="width:64px;height:64px;border-radius:50%;background:#d1fae5;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem">
                        <i class="bi bi-check-lg" style="font-size:2rem;color:#059669"></i>
                    </div>
                    <h5 class="fw-bold">Welcome to FastMessenger!</h5>
                    <p class="text-muted small">Your account has been created successfully.</p>
                    <a href="<?= BASE_URL ?>/index.php?r=<?= $randstr ?>" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-house-door"></i> Go to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script>
    let checkTimer;
    function checkUser(username) {
        clearTimeout(checkTimer);
        const el = document.getElementById('used');
        if (username.trim() === '') { el.innerHTML = ''; return; }
        checkTimer = setTimeout(() => {
            fetch('<?= BASE_URL ?>/ajax/checkuser.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'user=' + encodeURIComponent(username)
            })
            .then(r => r.text())
            .then(data => el.innerHTML = data);
        }, 350);
    }
    </script>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($signupSuccess)): ?>
    <script>
    new bootstrap.Modal(document.getElementById('successModal')).show();
    </script>
<?php endif; ?>
</body>
</html>
