<?php
require_once __DIR__ . '/../includes/header.php';

if (!$loggedin) {
    echo '</main></body></html>';
    exit;
}

$stmt    = queryMysql("SELECT text FROM profiles WHERE user=?", [$user]);
$existing = $stmt->fetch();
$text    = '';
$message = '';
$msgType = 'info';

if (isset($_POST['text'])) {
    $text = sanitizeString($_POST['text']);
    $text = preg_replace('/\s\s+/', ' ', $text);

    if ($existing) {
        queryMysql("UPDATE profiles SET text=? WHERE user=?", [$text, $user]);
    } else {
        queryMysql("INSERT INTO profiles (user, text) VALUES(?, ?)", [$user, $text]);
    }
    $message = 'Profile text updated!';
    $msgType = 'success';
} else {
    $text = $existing ? $existing['text'] : '';
}

if (isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '') {
    $allowed = ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'];

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Upload failed. Please try a smaller file.';
        $msgType = 'danger';
    } elseif (!in_array($_FILES['image']['type'], $allowed)) {
        $message = 'Invalid image type. Please use GIF, JPEG, or PNG.';
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
                $msgType = 'success';
            } else {
                $message = 'Could not process the image. Please try another file.';
                $msgType = 'danger';
            }
        } else {
            $message = 'Failed to save the uploaded file.';
            $msgType = 'danger';
        }
    }
}
?>
    <h2 class="section-title">Your Profile</h2>

<?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
        <i class="bi bi-<?= $msgType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
