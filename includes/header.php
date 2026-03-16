<?php
ob_start();
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

$userstr  = 'Welcome Guest';
$randstr  = substr(md5(random_bytes(8)), 0, 7);
$currPage = basename($_SERVER['PHP_SELF']);

if (isset($_SESSION['user'])) {
    $user     = $_SESSION['user'];
    $loggedin = true;
    $userstr  = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
} else {
    $loggedin = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FastMessenger<?= $loggedin ? " &mdash; $userstr" : '' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css?v=<?= time() ?>">
</head>
<body>
    <nav class="top-header">
        <div class="container">
            <div class="top-header-inner">
                <a class="top-header-brand" href="<?= BASE_URL ?>/index.php?r=<?= $randstr ?>">
                    <div class="brand-logo-circle">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div class="brand-text">
                        <span class="brand-name">FastMessenger</span>
                        <span class="brand-tagline">Connect &amp; Chat</span>
                    </div>
                </a>
<?php if ($loggedin): ?>
                <div class="top-header-user">
                    <?php if (file_exists(ROOT_DIR . '/uploads/' . $user . '.jpg')): ?>
                        <img src="<?= BASE_URL ?>/uploads/<?= rawurlencode($user) ?>.jpg" class="header-avatar" alt="<?= $userstr ?>">
                    <?php else: ?>
                        <div class="header-avatar-placeholder"><?= strtoupper(substr($user, 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="header-user-info">
                        <span class="header-user-name"><?= $userstr ?></span>
                        <span class="header-user-status"><i class="bi bi-circle-fill"></i> Online</span>
                    </div>
                </div>
<?php endif; ?>
            </div>
        </div>
    </nav>
<?php if ($loggedin): ?>
    <div class="tab-bar">
        <div class="container">
            <div class="tab-bar-inner">
                <a class="tab-item<?= $currPage === 'index.php' ? ' active' : '' ?>" href="<?= BASE_URL ?>/index.php?r=<?= $randstr ?>">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Home</span>
                </a>
                <a class="tab-item<?= $currPage === 'friends.php' ? ' active' : '' ?>" href="<?= BASE_URL ?>/pages/friends.php?r=<?= $randstr ?>">
                    <i class="bi bi-heart-fill"></i>
                    <span>Friends</span>
                </a>
                <a class="tab-item<?= $currPage === 'members.php' ? ' active' : '' ?>" href="<?= BASE_URL ?>/pages/members.php?r=<?= $randstr ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Members</span>
                </a>
                <a class="tab-item<?= $currPage === 'messages.php' ? ' active' : '' ?>" href="<?= BASE_URL ?>/pages/messages.php?r=<?= $randstr ?>">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>Messages</span>
                </a>
                <a class="tab-item<?= $currPage === 'profile.php' ? ' active' : '' ?>" href="<?= BASE_URL ?>/pages/profile.php?r=<?= $randstr ?>">
                    <i class="bi bi-person-fill"></i>
                    <span>Profile</span>
                </a>
                <a class="tab-item tab-logout" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="tab-bar">
        <div class="container">
            <div class="tab-bar-inner justify-content-center gap-2">
                <a class="tab-item<?= $currPage === 'index.php' ? ' active' : '' ?>" href="<?= BASE_URL ?>/index.php?r=<?= $randstr ?>">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Home</span>
                </a>
                <a class="tab-item<?= $currPage === 'signup.php' ? ' active' : '' ?>" href="<?= BASE_URL ?>/auth/signup.php?r=<?= $randstr ?>">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Sign Up</span>
                </a>
                <a class="tab-item<?= $currPage === 'login.php' ? ' active' : '' ?>" href="<?= BASE_URL ?>/auth/login.php?r=<?= $randstr ?>">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Log In</span>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if ($loggedin): ?>
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border:none;border-radius:14px">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-box-arrow-right text-danger" style="font-size:2.5rem"></i>
                    <h5 class="mt-3 fw-bold" id="logoutModalLabel">Log Out?</h5>
                    <p class="text-muted small">Are you sure you want to log out of FastMessenger?</p>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <a href="<?= BASE_URL ?>/auth/logout.php?r=<?= $randstr ?>" class="btn btn-danger px-4">
                            <i class="bi bi-box-arrow-right"></i> Log Out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
    <main class="container py-4">
