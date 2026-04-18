<?php
require_once __DIR__ . '/../includes/header.php';

if (!$loggedin) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$view = isset($_GET['view']) ? sanitizeString($_GET['view']) : $user;

if ($view === $user) {
    $name2 = 'Your';
    $name3 = 'You are';
} else {
    $viewSafe = htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
    $name2 = "$viewSafe's";
    $name3 = "$viewSafe is";
}

$iFollow  = [];  // people $view follows
$followMe = [];  // people who follow $view

$result = queryMysql("SELECT friend FROM friends WHERE user=?", [$view]);
while ($row = $result->fetch()) $iFollow[] = $row['friend'];

$result = queryMysql("SELECT user FROM friends WHERE friend=?", [$view]);
while ($row = $result->fetch()) $followMe[] = $row['user'];

$mutual    = array_intersect($iFollow, $followMe);
$following = array_diff($iFollow, $mutual);   // I follow them, they don't follow back
$followers = array_diff($followMe, $mutual);  // They follow me, I don't follow them
$hasFriends = false;
?>
    <h2 class="section-title"><?= $name2 ?> Friends</h2>

<?php
if (isset($_GET['remove'])) {
    $remove = sanitizeString($_GET['remove']);
    queryMysql("DELETE FROM friends WHERE user=? AND friend=?", [$user, $remove]);
    header("Location: " . BASE_URL . "/pages/friends.php?r=$randstr");
    exit;
}
?>

<?php if (count($mutual)): $hasFriends = true; ?>
    <div class="friend-section">
        <h5><i class="bi bi-arrow-left-right text-primary"></i> Mutual Friends</h5>
<?php foreach ($mutual as $friend):
    $initial = strtoupper($friend[0]);
?>
        <div class="member-card">
<?php if (file_exists(ROOT_DIR . '/uploads/' . $friend . '.jpg')): ?>
            <img src="<?= BASE_URL ?>/uploads/<?= rawurlencode($friend) ?>.jpg" class="member-avatar-img" alt="<?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>">
<?php else: ?>
            <div class="member-avatar"><?= $initial ?></div>
<?php endif; ?>
            <a href="<?= BASE_URL ?>/pages/members.php?view=<?= urlencode($friend) ?>&r=<?= $randstr ?>"
               class="fw-semibold text-decoration-none flex-grow-1">
                <?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>
            </a>
            <span class="badge bg-primary me-2">Mutual</span>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmRemove('<?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>', '<?= BASE_URL ?>/pages/friends.php?remove=<?= urlencode($friend) ?>&r=<?= $randstr ?>')">
                <i class="bi bi-person-dash"></i> Remove
            </button>
        </div>
<?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (count($followers)): $hasFriends = true; ?>
    <div class="friend-section">
        <h5><i class="bi bi-person-check text-info"></i> <?= $name2 ?> Followers</h5>
        <p class="text-muted small mb-3">People who follow <?= $view === $user ? 'you' : htmlspecialchars($view, ENT_QUOTES, 'UTF-8') ?>.</p>
<?php foreach ($followers as $friend):
    $initial = strtoupper($friend[0]);
?>
        <div class="member-card">
<?php if (file_exists(ROOT_DIR . '/uploads/' . $friend . '.jpg')): ?>
            <img src="<?= BASE_URL ?>/uploads/<?= rawurlencode($friend) ?>.jpg" class="member-avatar-img" alt="<?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>">
<?php else: ?>
            <div class="member-avatar"><?= $initial ?></div>
<?php endif; ?>
            <a href="<?= BASE_URL ?>/pages/members.php?view=<?= urlencode($friend) ?>&r=<?= $randstr ?>"
               class="fw-semibold text-decoration-none flex-grow-1">
                <?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>
            </a>
            <span class="badge bg-info">Follows You</span>
        </div>
<?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (count($following)): $hasFriends = true; ?>
    <div class="friend-section">
        <h5><i class="bi bi-person-plus text-warning"></i> <?= $name3 === 'You are' ? 'You are' : $name3 ?> Following</h5>
        <p class="text-muted small mb-3">People <?= $view === $user ? 'you follow' : htmlspecialchars($view, ENT_QUOTES, 'UTF-8') . ' follows' ?> who haven't followed back.</p>
<?php foreach ($following as $friend):
    $initial = strtoupper($friend[0]);
?>
        <div class="member-card">
<?php if (file_exists(ROOT_DIR . '/uploads/' . $friend . '.jpg')): ?>
            <img src="<?= BASE_URL ?>/uploads/<?= rawurlencode($friend) ?>.jpg" class="member-avatar-img" alt="<?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>">
<?php else: ?>
            <div class="member-avatar"><?= $initial ?></div>
<?php endif; ?>
            <a href="<?= BASE_URL ?>/pages/members.php?view=<?= urlencode($friend) ?>&r=<?= $randstr ?>"
               class="fw-semibold text-decoration-none flex-grow-1">
                <?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>
            </a>
            <span class="badge bg-warning text-dark me-2">Following</span>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmRemove('<?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>', '<?= BASE_URL ?>/pages/friends.php?remove=<?= urlencode($friend) ?>&r=<?= $randstr ?>')">
                <i class="bi bi-person-dash"></i> Remove
            </button>
        </div>
<?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!$hasFriends): ?>
    <div class="empty-state card mx-auto shadow-sm">
        <div class="card-body py-5 px-4">
            <div class="empty-state-icon mb-4">
                <i class="bi bi-people"></i>
            </div>
            <h5>No friends yet</h5>
            <p class="text-muted">Follow members to build your network and unlock private conversations.</p>
            <a href="<?= BASE_URL ?>/pages/members.php?r=<?= $randstr ?>" class="btn btn-primary btn-lg mt-3">
                <i class="bi bi-search me-2"></i> Browse Members
            </a>
        </div>
    </div>
<?php endif; ?>
    <div class="modal fade" id="removeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border:none;border-radius:14px">
                <div class="modal-body text-center p-4">
                    <div style="width:64px;height:64px;border-radius:50%;background:#fee2e2;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem">
                        <i class="bi bi-person-dash-fill" style="font-size:2rem;color:#dc2626"></i>
                    </div>
                    <h5 class="fw-bold">Remove <span id="removeName"></span>?</h5>
                    <p class="text-muted small">Are you sure you want to remove this friend?</p>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary flex-fill py-2" data-bs-dismiss="modal">No</button>
                        <a id="removeLink" href="#" class="btn btn-danger flex-fill py-2">Yes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmRemove(name, url) {
        document.getElementById('removeName').textContent = name;
        document.getElementById('removeLink').href = url;
        new bootstrap.Modal(document.getElementById('removeModal')).show();
    }
    </script>
</body>
</html>
