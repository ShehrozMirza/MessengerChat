<?php
require_once __DIR__ . '/../includes/header.php';

if (!$loggedin) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$stmt    = queryMysql("SELECT text FROM profiles WHERE user=?", [$user]);
$existing = $stmt->fetch();
$text    = '';
$message = '';
$msgType = 'info';

if (isset($_POST['username'])) {
    $new_username = sanitizeString($_POST['username']);
    if ($new_username !== '' && $new_username !== $user) {
        $stmt = queryMysql("SELECT user FROM members WHERE user=?", [$new_username]);
        if ($stmt->rowCount()) {
            $message = 'Username already taken.';
            $msgType = 'danger';
            $showModal = true;
        } else {
            global $pdo;
            try {
                $pdo->beginTransaction();
                queryMysql("UPDATE members SET user=? WHERE user=?", [$new_username, $user]);
                queryMysql("UPDATE messages SET auth=? WHERE auth=?", [$new_username, $user]);
                queryMysql("UPDATE messages SET recip=? WHERE recip=?", [$new_username, $user]);
                queryMysql("UPDATE friends SET user=? WHERE user=?", [$new_username, $user]);
                queryMysql("UPDATE friends SET friend=? WHERE friend=?", [$new_username, $user]);
                queryMysql("UPDATE profiles SET user=? WHERE user=?", [$new_username, $user]);
                queryMysql("UPDATE password_resets SET user=? WHERE user=?", [$new_username, $user]);
                $pdo->commit();
                if (file_exists(ROOT_DIR . "/uploads/$user.jpg")) {
                    rename(ROOT_DIR . "/uploads/$user.jpg", ROOT_DIR . "/uploads/$new_username.jpg");
                }
                $_SESSION['user'] = $new_username;
                $user = $new_username;
                $message = 'Username updated successfully.';
                $msgType = 'success';
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'Username update failed. Please try again.';
                $msgType = 'danger';
            }
            $showModal = true;
        }
    }
}

if (isset($_POST['text'])) {
    $text = sanitizeString($_POST['text']);
    $text = preg_replace('/\s\s+/', ' ', $text);

    if ($existing) {
        queryMysql("UPDATE profiles SET text=? WHERE user=?", [$text, $user]);
    } else {
        queryMysql("INSERT INTO profiles (user, text) VALUES(?, ?)", [$user, $text]);
    }
    $message = 'Profile text updated!';
    $showModal = true;
    $msgType = 'success';
} else {
    $text = $existing ? $existing['text'] : '';
}

if (isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '') {
    $allowed = ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/webp'];

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Upload failed. Please try a smaller file.';
        $msgType = 'danger';
        $showModal = true;
    } elseif (!in_array($_FILES['image']['type'], $allowed)) {
        $message = 'Invalid image type. Please use GIF, JPEG, or PNG.';
        $showModal = true;
        $msgType = 'danger';
    } else {
        $saveto = ROOT_DIR . "/uploads/$user.jpg";

        if (move_uploaded_file($_FILES['image']['tmp_name'], $saveto)) {
            $src = null;
            switch ($_FILES['image']['type']) {
                case 'image/gif':   $src = @imagecreatefromgif($saveto);  break;
                case 'image/jpeg':
                case 'image/pjpeg': $src = @imagecreatefromjpeg($saveto); break;
                case 'image/png':   $src = @imagecreatefrompng($saveto);  break;
                case 'image/webp':  $src = @imagecreatefromwebp($saveto); break;
            }

            if ($src) {
                $w   = imagesx($src);
                $h   = imagesy($src);
                $max = 200;
                $tw  = $w;
                $th  = $h;

                if ($w > $h && $max < $w)     { $th = (int)($max / $w * $h); $tw = $max; }
                elseif ($h > $w && $max < $h) { $tw = (int)($max / $h * $w); $th = $max; }
                elseif ($max < $w)            { $tw = $th = $max; }

                $tmp = imagecreatetruecolor((int)$tw, (int)$th);
                imagecopyresampled($tmp, $src, 0, 0, 0, 0, (int)$tw, (int)$th, $w, $h);
                imagejpeg($tmp, $saveto, 90);
                imagedestroy($tmp);
                imagedestroy($src);

                $message = 'Profile image updated!';
                $showModal = true;
                $msgType = 'success';
            } else {
                $showModal = true;
                $message = 'Could not process the image. Please try another file.';
                $msgType = 'danger';
            }
        } else {
            $showModal = true;
            $message = 'Failed to save the uploaded file.';
            $msgType = 'danger';
        }
    }
}
?>
    <h2 class="section-title">Your Profile</h2>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center p-4">
                    <h5 class="mb-3 fw-semibold">Preview</h5>
                    <?php showProfile($user); ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="mb-3 fw-semibold">Edit Profile</h5>
                    <form method="post" action="<?= BASE_URL ?>/pages/profile.php?r=<?= $randstr ?>" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user, ENT_QUOTES, 'UTF-8') ?>" maxlength="16">
                            <div class="form-text">Leave blank or unchanged to keep current username.</div>
                        </div>
                        <div class="mb-3">
                            <label for="text" class="form-label">About You</label>
                            <textarea class="form-control" id="text" name="text" rows="4"><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Accepted formats: GIF, JPEG, PNG</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Save Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border:none;border-radius:14px">
                <div class="modal-body text-center p-4">
                    <div style="width:64px;height:64px;border-radius:50%;background:<?= $msgType === 'success' ? '#d1fae5' : '#fee2e2' ?>;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem">
                        <i class="bi bi-<?= $msgType === 'success' ? 'check-lg' : 'exclamation-triangle' ?>" style="font-size:2rem;color:<?= $msgType === 'success' ? '#059669' : '#dc2626' ?>"></i>
                    </div>
                    <h5 class="fw-bold"><?= $msgType === 'success' ? 'Profile Updated!' : 'Update Failed' ?></h5>
                    <p class="text-muted small"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
                    <button type="button" class="btn btn-primary w-100 py-2" data-bs-dismiss="modal">
                        <i class="bi bi-check-lg"></i> OK
                    </button>
                </div>
            </div>
        </div>
    </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($showModal)): ?>
    <script>
    new bootstrap.Modal(document.getElementById('profileModal')).show();
    </script>
<?php endif; ?>
</body>
</html>
