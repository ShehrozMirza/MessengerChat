<?php
require_once 'header.php';

if (!$loggedin) {
    echo '</main></body></html>';
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

$followers = [];
$following = [];

$result = queryMysql("SELECT friend FROM friends WHERE user=?", [$view]);
while ($row = $result->fetch()) $followers[] = $row['friend'];

$result = queryMysql("SELECT user FROM friends WHERE friend=?", [$view]);
while ($row = $result->fetch()) $following[] = $row['user'];

$mutual    = array_intersect($followers, $following);
$followers = array_diff($followers, $mutual);
$following = array_diff($following, $mutual);
$hasFriends = false;
?>
    <h2 class="section-title"><?= $name2 ?> Friends</h2>

<?php if (count($mutual)): $hasFriends = true; ?>
    <div class="friend-section">
        <h5><i class="bi bi-arrow-left-right text-primary"></i> Mutual Friends</h5>
<?php foreach ($mutual as $friend):
    $initial = strtoupper($friend[0]);
?>
        <div class="member-card">
<?php if (file_exists('uploads/' . $friend . '.jpg')): ?>
            <img src="uploads/<?= rawurlencode($friend) ?>.jpg" class="member-avatar-img" alt="<?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>">
<?php else: ?>
            <div class="member-avatar"><?= $initial ?></div>
<?php endif; ?>
            <a href="members.php?view=<?= urlencode($friend) ?>&r=<?= $randstr ?>"
               class="fw-semibold text-decoration-none flex-grow-1">
                <?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>
            </a>
            <span class="badge bg-primary">Mutual</span>
        </div>
<?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (count($followers)): $hasFriends = true; ?>
    <div class="friend-section">
        <h5><i class="bi bi-arrow-left text-info"></i> <?= $name2 ?> Followers</h5>
<?php foreach ($followers as $friend):
    $initial = strtoupper($friend[0]);
?>
        <div class="member-card">
<?php if (file_exists('uploads/' . $friend . '.jpg')): ?>
            <img src="uploads/<?= rawurlencode($friend) ?>.jpg" class="member-avatar-img" alt="<?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>">
<?php else: ?>
            <div class="member-avatar"><?= $initial ?></div>
<?php endif; ?>
            <a href="members.php?view=<?= urlencode($friend) ?>&r=<?= $randstr ?>"
               class="fw-semibold text-decoration-none flex-grow-1">
                <?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>
            </a>
            <span class="badge bg-info">Follower</span>
        </div>
<?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (count($following)): $hasFriends = true; ?>
    <div class="friend-section">
        <h5><i class="bi bi-arrow-right text-warning"></i> <?= $name3 ?> Following</h5>
<?php foreach ($following as $friend):
    $initial = strtoupper($friend[0]);
?>
        <div class="member-card">
<?php if (file_exists('uploads/' . $friend . '.jpg')): ?>
            <img src="uploads/<?= rawurlencode($friend) ?>.jpg" class="member-avatar-img" alt="<?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>">
<?php else: ?>
            <div class="member-avatar"><?= $initial ?></div>
<?php endif; ?>
            <a href="members.php?view=<?= urlencode($friend) ?>&r=<?= $randstr ?>"
               class="fw-semibold text-decoration-none flex-grow-1">
                <?= htmlspecialchars($friend, ENT_QUOTES, 'UTF-8') ?>
            </a>
            <span class="badge bg-warning text-dark">Following</span>
        </div>
<?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!$hasFriends): ?>
    <div class="empty-state">
        <i class="bi bi-people d-block"></i>
        <h5>No friends yet</h5>
        <p class="text-muted">Start following members to build your network!</p>
        <a href="members.php?r=<?= $randstr ?>" class="btn btn-primary">
            <i class="bi bi-search"></i> Browse Members
        </a>
    </div>
<?php endif; ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
