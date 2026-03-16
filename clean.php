<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FastMessenger &mdash; Clean Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card mx-auto" style="max-width:600px; border:none; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,0.06)">
            <div class="card-body p-4">
                <h3 class="mb-4"><i class="bi bi-trash text-danger"></i> Clean Database</h3>
<?php
require_once 'functions.php';

$tables = ['password_resets', 'profiles', 'friends', 'messages', 'members'];

foreach ($tables as $table) {
    queryMysql("DROP TABLE IF EXISTS $table");
    echo "<div class='alert alert-warning py-2'><i class='bi bi-trash'></i> Dropped table <strong>$table</strong></div>";
}

echo "<hr>";

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
?>
                <hr>
                <div class="alert alert-success py-2">
                    <i class="bi bi-check-circle"></i> Database cleaned and recreated!
                </div>
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-house-door"></i> Go to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
