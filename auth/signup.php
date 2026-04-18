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
    <style>
    .pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    </style>
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
                        <div class="position-relative">
                            <input type="password" class="form-control" id="pass" name="pass"
                                   maxlength="255" required oninput="validatePassword()" onfocus="showPassPopover()" onblur="hidePassPopover()">
                            <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3 text-muted" tabindex="-1" onmousedown="return false" onclick="togglePassVis()">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        <div id="passPopover" class="pass-popover d-none">
                            <div class="pass-popover-title">Password Requirements</div>
                            <div class="pass-strength-bar mb-3">
                                <div class="pass-strength-fill" id="strengthFill"></div>
                            </div>
                            <div class="pass-strength-label mb-3" id="strengthLabel">Enter a password</div>
                            <ul class="pass-checklist">
                                <li id="req-len"><i class="bi bi-x-circle-fill"></i> At least 8 characters</li>
                                <li id="req-upper"><i class="bi bi-x-circle-fill"></i> One uppercase letter</li>
                                <li id="req-lower"><i class="bi bi-x-circle-fill"></i> One lowercase letter</li>
                                <li id="req-digit"><i class="bi bi-x-circle-fill"></i> One number</li>
                                <li id="req-special"><i class="bi bi-x-circle-fill"></i> One special character (!@#$...)</li>
                            </ul>
                        </div>
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
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content success-modal-card">
                <canvas id="confettiCanvas"></canvas>
                <div class="success-orbs">
                    <div class="orb orb-1"></div>
                    <div class="orb orb-2"></div>
                    <div class="orb orb-3"></div>
                </div>
                <div class="modal-body text-center p-5 position-relative">
                    <div class="success-icon-wrap mb-4">
                        <svg class="success-ring" viewBox="0 0 100 100">
                            <circle class="success-ring-bg" cx="50" cy="50" r="44"/>
                            <circle class="success-ring-fill" cx="50" cy="50" r="44"/>
                        </svg>
                        <i class="bi bi-check-lg success-check-icon"></i>
                    </div>
                    <div class="success-badge mb-3">
                        <i class="bi bi-stars"></i> Account Created
                    </div>
                    <h3 class="fw-bold text-white mb-2" style="font-size:1.75rem;letter-spacing:-0.5px">
                        Welcome aboard!
                    </h3>
                    <p class="mb-4" style="color:rgba(255,255,255,0.75);font-size:0.97rem;line-height:1.7;max-width:320px;margin:0 auto 1.5rem">
                        Your FastMessenger account is ready. Start connecting, messaging, and building your network.
                    </p>
                    <a href="<?= BASE_URL ?>/index.php?r=<?= $randstr ?>" class="btn success-enter-btn">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Enter FastMessenger
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

    function showPassPopover() { document.getElementById('passPopover').classList.remove('d-none'); }
    function hidePassPopover() { document.getElementById('passPopover').classList.add('d-none'); }

    function togglePassVis() {
        const inp = document.getElementById('pass');
        const ico = document.getElementById('eyeIcon');
        if (inp.type === 'password') { inp.type = 'text';     ico.className = 'bi bi-eye-slash'; }
        else                         { inp.type = 'password'; ico.className = 'bi bi-eye'; }
    }

    function setReq(id, ok) {
        const el = document.getElementById(id);
        el.querySelector('i').className = ok ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill';
        el.className = ok ? 'pass-req-ok' : '';
    }

    function validatePassword() {
        const pass = document.getElementById('pass').value;
        const checks = {
            len:     pass.length >= 8,
            upper:   /[A-Z]/.test(pass),
            lower:   /[a-z]/.test(pass),
            digit:   /[0-9]/.test(pass),
            special: /[^a-zA-Z0-9]/.test(pass),
        };

        setReq('req-len',     checks.len);
        setReq('req-upper',   checks.upper);
        setReq('req-lower',   checks.lower);
        setReq('req-digit',   checks.digit);
        setReq('req-special', checks.special);

        const score = Object.values(checks).filter(Boolean).length;
        const fill  = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        const levels = [
            { pct: '0%',   cls: '',                  text: 'Enter a password' },
            { pct: '20%',  cls: 'strength-very-weak',text: 'Very Weak' },
            { pct: '40%',  cls: 'strength-weak',     text: 'Weak' },
            { pct: '60%',  cls: 'strength-fair',     text: 'Fair' },
            { pct: '80%',  cls: 'strength-good',     text: 'Good' },
            { pct: '100%', cls: 'strength-strong',   text: 'Strong' },
        ];
        const lvl = pass.length === 0 ? levels[0] : levels[score];
        fill.style.width = lvl.pct;
        fill.className = 'pass-strength-fill ' + lvl.cls;
        label.textContent = lvl.text;
        label.className = 'pass-strength-label mb-3 ' + lvl.cls;

        return score === 5;
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        if (!validatePassword()) {
            e.preventDefault();
            showPassPopover();
            document.getElementById('pass').focus();
        }
    });
    </script>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($signupSuccess)): ?>
    <script>
    new bootstrap.Modal(document.getElementById('successModal')).show();

    (function(){
        const canvas = document.getElementById('confettiCanvas');
        const ctx    = canvas.getContext('2d');
        const modal  = document.querySelector('.success-modal-card');
        function resize(){
            canvas.width  = modal.offsetWidth;
            canvas.height = modal.offsetHeight;
        }
        resize();

        const colors = ['#a78bfa','#818cf8','#38bdf8','#34d399','#fbbf24','#f472b6','#fff'];
        const pieces = Array.from({length:90}, () => ({
            x:   Math.random() * canvas.width,
            y:   Math.random() * canvas.height * -1,
            r:   Math.random() * 6 + 3,
            d:   Math.random() * 3 + 1.5,
            color: colors[Math.floor(Math.random() * colors.length)],
            tilt: Math.random() * 10 - 5,
            tiltSpeed: Math.random() * 0.08 + 0.03,
            angle: 0,
            shape: Math.random() > 0.5 ? 'rect' : 'circle',
        }));

        let frame = 0;
        function draw(){
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            pieces.forEach(p => {
                p.angle += p.tiltSpeed;
                p.y += p.d;
                p.x += Math.sin(p.angle) * 1.2;
                if (p.y > canvas.height) { p.y = -10; p.x = Math.random() * canvas.width; }
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.angle);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = 0.85;
                if (p.shape === 'rect') ctx.fillRect(-p.r/2, -p.r/2, p.r, p.r * 1.6);
                else { ctx.beginPath(); ctx.arc(0, 0, p.r/2, 0, Math.PI*2); ctx.fill(); }
                ctx.restore();
            });
            frame++;
            if (frame < 420) requestAnimationFrame(draw);
            else ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
        setTimeout(draw, 300);
    })();
    </script>
<?php endif; ?>
</body>
</html>
