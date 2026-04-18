<?php
require_once __DIR__ . '/../includes/header.php';

if (!$loggedin) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

if (isset($_GET['view'])) {
    $view = sanitizeString($_GET['view']);
    $name = ($view === $user) ? 'Your' : htmlspecialchars($view, ENT_QUOTES, 'UTF-8') . "'s";
?>
    <h2 class="section-title"><?= $name ?> Profile</h2>
    <div class="card mb-4">
        <div class="card-body p-4">
            <?php showProfile($view); ?>
        </div>
    </div>
    <a href="<?= BASE_URL ?>/pages/messages.php?view=<?= urlencode($view) ?>&r=<?= $randstr ?>" class="btn btn-primary">
        <i class="bi bi-envelope"></i> View <?= $name ?> Messages
    </a>
<?php
    echo '</main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>';
    exit;
}

$friendAction = '';
$friendTarget = '';

if (isset($_GET['add'])) {
    $add  = sanitizeString($_GET['add']);
    $stmt = queryMysql("SELECT * FROM friends WHERE user=? AND friend=?", [$user, $add]);
    if (!$stmt->rowCount()) {
        queryMysql("INSERT INTO friends VALUES(?, ?)", [$user, $add]);
    }
    $friendAction = 'followed';
    $friendTarget = htmlspecialchars($add, ENT_QUOTES, 'UTF-8');
} elseif (isset($_GET['remove'])) {
    $remove = sanitizeString($_GET['remove']);
    queryMysql("DELETE FROM friends WHERE user=? AND friend=?", [$user, $remove]);
    $friendAction = 'unfollowed';
    $friendTarget = htmlspecialchars($remove, ENT_QUOTES, 'UTF-8');
}

$result = queryMysql("SELECT user FROM members ORDER BY user");
?>
    <h2 class="section-title">Members</h2>

<?php while ($row = $result->fetch()):
    if ($row['user'] === $user) continue;
    $member  = htmlspecialchars($row['user'], ENT_QUOTES, 'UTF-8');
    $initial = strtoupper($member[0]);

    $s1 = queryMysql("SELECT * FROM friends WHERE user=? AND friend=?", [$row['user'], $user]);
    $t1 = $s1->rowCount();

    $s2 = queryMysql("SELECT * FROM friends WHERE user=? AND friend=?", [$user, $row['user']]);
    $t2 = $s2->rowCount();
?>
    <div class="member-card">
<?php if (file_exists(ROOT_DIR . '/uploads/' . $row['user'] . '.jpg')): ?>
        <img src="<?= BASE_URL ?>/uploads/<?= rawurlencode($row['user']) ?>.jpg" class="member-avatar-img" alt="<?= $member ?>">
<?php else: ?>
        <div class="member-avatar"><?= $initial ?></div>
<?php endif; ?>
        <div class="flex-grow-1">
            <a href="<?= BASE_URL ?>/pages/members.php?view=<?= urlencode($row['user']) ?>&r=<?= $randstr ?>"
               class="fw-semibold text-decoration-none"><?= $member ?></a>
<?php if (($t1 + $t2) > 1): ?>
            <span class="badge bg-primary ms-2">Mutual Friend</span>
<?php elseif ($t1): ?>
            <span class="badge bg-warning text-dark ms-2">Follows You</span>
<?php elseif ($t2): ?>
            <span class="badge bg-info ms-2">Following</span>
<?php endif; ?>
        </div>
        <div>
<?php if (!$t2): ?>
            <a href="<?= BASE_URL ?>/pages/members.php?add=<?= urlencode($row['user']) ?>&r=<?= $randstr ?>"
               class="btn btn-sm btn-outline-primary">
                <i class="bi bi-person-plus"></i> <?= $t2 ? 'Follow Back' : 'Follow' ?>
            </a>
<?php else: ?>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="confirmUnfollow('<?= htmlspecialchars($row['user'], ENT_QUOTES, 'UTF-8') ?>', '<?= BASE_URL ?>/pages/members.php?remove=<?= urlencode($row['user']) ?>&r=<?= $randstr ?>')">
                <i class="bi bi-person-dash"></i> Unfollow
            </button>
<?php endif; ?>
        </div>
    </div>
<?php endwhile; ?>
    <div class="modal fade" id="unfollowModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border:none;border-radius:14px">
                <div class="modal-body text-center p-4">
                    <div style="width:64px;height:64px;border-radius:50%;background:#fef3c7;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem">
                        <i class="bi bi-person-dash-fill" style="font-size:2rem;color:#d97706"></i>
                    </div>
                    <h5 class="fw-bold">Unfollow <span id="unfollowName"></span>?</h5>
                    <p class="text-muted small">Are you sure you want to unfollow this person?</p>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary flex-fill py-2" data-bs-dismiss="modal">
                            No
                        </button>
                        <a id="unfollowLink" href="#" class="btn btn-danger flex-fill py-2">
                            Yes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php if ($friendAction): ?>
    <div class="modal fade" id="friendModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border:none;border-radius:14px">
                <div class="modal-body text-center p-4">
<?php if ($friendAction === 'followed'): ?>
                    <div style="width:64px;height:64px;border-radius:50%;background:#d1fae5;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem">
                        <i class="bi bi-person-check-fill" style="font-size:2rem;color:#059669"></i>
                    </div>
                    <h5 class="fw-bold">Following <?= $friendTarget ?>!</h5>
                    <p class="text-muted small">You are now following <?= $friendTarget ?>.</p>
<?php else: ?>
                    <div style="width:64px;height:64px;border-radius:50%;background:#fee2e2;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem">
                        <i class="bi bi-person-dash-fill" style="font-size:2rem;color:#dc2626"></i>
                    </div>
                    <h5 class="fw-bold">Unfollowed <?= $friendTarget ?></h5>
                    <p class="text-muted small">You are no longer following <?= $friendTarget ?>.</p>
<?php endif; ?>
                    <button type="button" class="btn btn-primary w-100 py-2" data-bs-dismiss="modal">
                        <i class="bi bi-check-lg"></i> OK
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmUnfollow(name, url) {
        document.getElementById('unfollowName').textContent = name;
        document.getElementById('unfollowLink').href = url;
        new bootstrap.Modal(document.getElementById('unfollowModal')).show();
    }
    </script>
<?php if ($friendAction): ?>
    <script>new bootstrap.Modal(document.getElementById('friendModal')).show();</script>
<?php endif; ?>
</body>
</html>
