<?php
require_once __DIR__ . '/includes/header.php';
date_default_timezone_set('UTC');
?>
<?php if ($loggedin): ?>
    <div class="row g-4">

        <div class="col-lg-8">

            <?php
            $publicMsgs = queryMysql(
                "SELECT * FROM messages WHERE pm='0' ORDER BY time DESC LIMIT 5"
            )->fetchAll();
            ?>
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center px-4 py-3">
                    <i class="bi bi-globe text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold">Recent Public Messages</h6>
                    <a href="<?= BASE_URL ?>/pages/messages.php?r=<?= $randstr ?>" class="ms-auto small text-decoration-none">View all</a>
                </div>
                <?php if ($publicMsgs): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($publicMsgs as $msg):
                            $authSafe = htmlspecialchars($msg['auth'], ENT_QUOTES, 'UTF-8');
                            $initial  = strtoupper($msg['auth'][0]);
                            $href     = BASE_URL . '/pages/messages.php?view=' . urlencode($msg['auth']) . '&r=' . $randstr;
                        ?>
                        <a href="<?= $href ?>" class="list-group-item list-group-item-action d-flex align-items-start gap-3 px-4 py-3 text-decoration-none">
                            <?php if (file_exists(ROOT_DIR . '/uploads/' . $msg['auth'] . '.jpg')): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= rawurlencode($msg['auth']) ?>.jpg" class="member-avatar-img" alt="<?= $authSafe ?>">
                            <?php else: ?>
                                <div class="member-avatar"><?= $initial ?></div>
                            <?php endif; ?>
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold small text-body"><?= $authSafe ?></span>
                                    <span class="text-muted" style="font-size:.75rem"><?= date('M j, g:ia', $msg['time']) ?></span>
                                </div>
                                <p class="mb-0 small text-muted text-truncate">
                                    <?= htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (empty($msg['message']) && !empty($msg['image'])): ?>
                                        <i class="bi bi-image"></i> Image
                                    <?php elseif (empty($msg['message']) && !empty($msg['audio'])): ?>
                                        <i class="bi bi-mic"></i> Voice message
                                    <?php endif; ?>
                                </p>
                            </div>
                            <i class="bi bi-chevron-right text-muted align-self-center" style="font-size:.75rem"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-chat-dots d-block mb-2" style="font-size:2rem;opacity:.4"></i>
                        <p class="mb-0">No public messages yet</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php
            $privateMsgs = queryMysql(
                "SELECT * FROM messages WHERE pm != '0' AND (auth=? OR recip=?) ORDER BY time DESC LIMIT 5",
                [$user, $user]
            )->fetchAll();
            ?>
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center px-4 py-3">
                    <i class="bi bi-lock text-primary me-2"></i>
                    <h6 class="mb-0 fw-semibold">Recent Private Messages</h6>
                    <a href="<?= BASE_URL ?>/pages/messages.php?r=<?= $randstr ?>" class="ms-auto small text-decoration-none">View all</a>
                </div>
                <?php if ($privateMsgs): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($privateMsgs as $msg):
                            $other   = ($msg['auth'] === $user) ? $msg['recip'] : $msg['auth'];
                            $isMine  = ($msg['auth'] === $user);
                            $otherSafe = htmlspecialchars($other, ENT_QUOTES, 'UTF-8');
                            $initial   = strtoupper($other[0]);
                            $text      = trim($msg['message']);
                            $preview   = $text !== ''
                                ? htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
                                : (!empty($msg['image']) ? '<i class="bi bi-image"></i> Image' : '<i class="bi bi-mic"></i> Voice message');
                            $label     = $isMine ? '<span class="text-primary fw-semibold">You:</span> ' : '';
                            $href      = BASE_URL . '/pages/messages.php?view=' . urlencode($other) . '&pm=1&r=' . $randstr;
                        ?>
                        <a href="<?= $href ?>" class="list-group-item list-group-item-action d-flex align-items-start gap-3 px-4 py-3 text-decoration-none">
                            <?php if (file_exists(ROOT_DIR . '/uploads/' . $other . '.jpg')): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= rawurlencode($other) ?>.jpg" class="member-avatar-img" alt="<?= $otherSafe ?>">
                            <?php else: ?>
                                <div class="member-avatar"><?= $initial ?></div>
                            <?php endif; ?>
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold small text-body d-flex align-items-center gap-1">
                                        <?= $otherSafe ?>
                                        <i class="bi bi-lock-fill text-success" style="font-size:.65rem"></i>
                                    </span>
                                    <span class="text-muted" style="font-size:.75rem"><?= date('M j, g:ia', $msg['time']) ?></span>
                                </div>
                                <p class="mb-0 small text-muted text-truncate"><?= $label . $preview ?></p>
                            </div>
                            <i class="bi bi-chevron-right text-muted align-self-center" style="font-size:.75rem"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-lock d-block mb-2" style="font-size:2rem;opacity:.4"></i>
                        <p class="mb-0">No private messages yet</p>
                        <p class="small mt-2">Send a private message to someone from their profile.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- /col-lg-8 -->

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center p-4">
                    <?php if (file_exists(ROOT_DIR . '/uploads/' . $user . '.jpg')): ?>
                        <img src="<?= BASE_URL ?>/uploads/<?= rawurlencode($user) ?>.jpg" class="rounded-circle mb-3" width="72" height="72"
                             style="object-fit:cover;border:3px solid var(--primary-light)" alt="<?= $userstr ?>">
                    <?php else: ?>
                        <div class="member-avatar mx-auto mb-3" style="width:72px;height:72px;min-width:72px;font-size:1.8rem">
                            <?= strtoupper($user[0]) ?>
                        </div>
                    <?php endif; ?>
                    <h5 class="fw-bold mb-1"><?= $userstr ?></h5>
                    <?php
                    $friendCount = queryMysql("SELECT COUNT(*) as c FROM friends WHERE user=? OR friend=?", [$user, $user])->fetch();
                    $msgCount    = queryMysql("SELECT COUNT(*) as c FROM messages WHERE recip=? OR auth=?", [$user, $user])->fetch();
                    ?>
                    <div class="d-flex justify-content-center gap-4 mt-3 small text-muted">
                        <div><strong class="text-body"><?= $friendCount['c'] ?></strong><br>Friends</div>
                        <div><strong class="text-body"><?= $msgCount['c'] ?></strong><br>Messages</div>
                    </div>
                    <a href="<?= BASE_URL ?>/pages/profile.php?r=<?= $randstr ?>" class="btn btn-outline-primary btn-sm mt-3">
                        <i class="bi bi-pencil-square"></i> Edit Profile
                    </a>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/pages/members.php?r=<?= $randstr ?>" class="btn btn-primary">
                    <i class="bi bi-people"></i> Members
                </a>
                <a href="<?= BASE_URL ?>/pages/friends.php?r=<?= $randstr ?>" class="btn btn-outline-primary">
                    <i class="bi bi-heart"></i> Friends
                </a>
                <a href="<?= BASE_URL ?>/pages/messages.php?r=<?= $randstr ?>" class="btn btn-outline-primary">
                    <i class="bi bi-envelope"></i> Messages
                </a>
            </div>
        </div><!-- /col-lg-4 -->

    </div><!-- /row -->
<?php else: ?>
    <div class="hero-section">
        <h1><i class="bi bi-lightning-charge-fill" style="color:#f59e0b"></i> FastMessenger</h1>
        <p>Lightning-fast messaging, real connections. Join our community today!</p>
        <a href="<?= BASE_URL ?>/auth/signup.php?r=<?= $randstr ?>" class="btn btn-accent btn-lg me-2">
            <i class="bi bi-person-plus"></i> Sign Up
        </a>
        <a href="<?= BASE_URL ?>/auth/login.php?r=<?= $randstr ?>" class="btn btn-light btn-lg">
            <i class="bi bi-box-arrow-in-right"></i> Log In
        </a>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="card text-center p-4">
                <i class="bi bi-chat-dots text-primary" style="font-size:2.5rem"></i>
                <h5 class="mt-3">Share Messages</h5>
                <p class="text-muted mb-0">Post public messages or send private whispers to your friends.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-4">
                <i class="bi bi-people-fill text-primary" style="font-size:2.5rem"></i>
                <h5 class="mt-3">Build Connections</h5>
                <p class="text-muted mb-0">Follow members, build friendships, and grow your network.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-4">
                <i class="bi bi-person-badge text-primary" style="font-size:2.5rem"></i>
                <h5 class="mt-3">Your Profile</h5>
                <p class="text-muted mb-0">Customize your profile with an image and bio to stand out.</p>
            </div>
        </div>
    </div>
<?php endif; ?>
    <div class="page-footer">
        FastMessenger &mdash; Lightning-fast social networking
    </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
