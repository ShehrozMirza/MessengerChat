<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FastMessenger &mdash; Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card mx-auto" style="max-width:600px; border:none; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,0.06)">
            <div class="card-body p-4">
                <h3 class="mb-4"><i class="bi bi-database-gear text-primary"></i> Database Setup</h3>
<?php
require_once 'functions.php';

createTable('members',
    'user VARCHAR(16),
    pass VARCHAR(255),
    email VARCHAR(255),
    INDEX(user(6))');

createTable('messages',
    'id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auth VARCHAR(16),
    recip VARCHAR(16),
    pm CHAR(1),
    time INT UNSIGNED,
    message VARCHAR(4096),
    INDEX(auth(6)),
    INDEX(recip(6))');

createTable('friends',
    'user VARCHAR(16),
    friend VARCHAR(16),
    INDEX(user(6)),
    INDEX(friend(6))');

createTable('profiles',
    'user VARCHAR(16),
    text VARCHAR(4096),
    INDEX(user(6))');

createTable('password_resets',
    'id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(16),
    token VARCHAR(64),
    expires INT UNSIGNED,
    INDEX(token(12))');

try {
    queryMysql("ALTER TABLE members MODIFY pass VARCHAR(255)");
    echo "<div class='alert alert-info py-2'><i class='bi bi-info-circle'></i> Upgraded <strong>pass</strong> column to VARCHAR(255) for bcrypt.</div>";
} catch (Exception $e) {}

try {
    queryMysql("ALTER TABLE members ADD COLUMN email VARCHAR(255) AFTER pass");
    echo "<div class='alert alert-info py-2'><i class='bi bi-info-circle'></i> Added <strong>email</strong> column to members table.</div>";
} catch (Exception $e) {}

try {
    queryMysql("ALTER TABLE messages ADD COLUMN image VARCHAR(255) AFTER message");
    echo "<div class='alert alert-info py-2'><i class='bi bi-info-circle'></i> Added <strong>image</strong> column to messages table.</div>";
} catch (Exception $e) {}

try {
    queryMysql("ALTER TABLE messages ADD COLUMN audio VARCHAR(255) AFTER image");
    echo "<div class='alert alert-info py-2'><i class='bi bi-info-circle'></i> Added <strong>audio</strong> column to messages table.</div>";
} catch (Exception $e) {}

if (isset($_GET['clean']) && $_GET['clean'] === 'yes') {
    queryMysql("DELETE FROM messages");
    queryMysql("DELETE FROM friends");
    queryMysql("DELETE FROM profiles");
    queryMysql("DELETE FROM password_resets");
    echo "<div class='alert alert-warning py-2'><i class='bi bi-trash'></i> Cleared all <strong>messages, friends, profiles &amp; password resets</strong>.</div>";
}

if (isset($_GET['cleanall']) && $_GET['cleanall'] === 'yes') {
    queryMysql("DELETE FROM messages");
    queryMysql("DELETE FROM friends");
    queryMysql("DELETE FROM profiles");
    queryMysql("DELETE FROM password_resets");
    queryMysql("DELETE FROM members");
    echo "<div class='alert alert-danger py-2'><i class='bi bi-trash'></i> Cleared <strong>ALL data</strong> including member accounts.</div>";
}
?>
                <hr>
                <div class="alert alert-success py-2">
                    <i class="bi bi-check-circle"></i> Setup complete!
                </div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house-door"></i> Go to Home
                    </a>
                    <a href="setup.php?clean=yes" class="btn btn-warning"
                       onclick="return confirm('Clear all messages, friends & profiles? Member accounts will be kept.')">
                        <i class="bi bi-trash"></i> Clean Data
                    </a>
                    <a href="setup.php?cleanall=yes" class="btn btn-danger"
                       onclick="return confirm('DELETE EVERYTHING including all member accounts? This cannot be undone!')">
                        <i class="bi bi-exclamation-triangle"></i> Reset All
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
