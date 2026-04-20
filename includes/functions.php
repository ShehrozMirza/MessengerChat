<?php
$db_host = 'localhost';
$db_name = 'robinsnest';
$db_user = 'root';
$db_pass = '';
$db_chrs = 'utf8mb4';
$attr = "mysql:host=$db_host;dbname=$db_name;charset=$db_chrs";
$opts =
[
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_FOUND_ROWS   => true,
];

try {
    $pdo = new PDO($attr, $db_user, $db_pass, $opts);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

function createTable($name, $query)
{
    queryMysql("CREATE TABLE IF NOT EXISTS $name($query)");
    echo "<div class='alert alert-success py-2'>Table <strong>$name</strong> created or already exists.</div>";
}

function queryMysql($query, $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

function destroySession()
{
    $_SESSION = [];
    if (session_id() !== '' || isset($_COOKIE[session_name()]))
        setcookie(session_name(), '', time() - 3600, '/');
    session_regenerate_id(true);
    session_destroy();
}

function sanitizeString($var)
{
    return strip_tags(trim($var));
}

function showProfile($user)
{
    global $pdo;
    if (file_exists(ROOT_DIR . "/uploads/$user.jpg")) {
        $imgSrc = BASE_URL . "/uploads/" . rawurlencode($user) . ".jpg?v=" . filemtime(ROOT_DIR . "/uploads/$user.jpg");
    } else {
        $gstmt = $pdo->prepare("SELECT gender FROM members WHERE user=?");
        $gstmt->execute([$user]);
        $grow = $gstmt->fetch();
        $gender = ($grow && $grow['gender'] === 'F') ? 'female' : 'male';
        $imgSrc = BASE_URL . "/uploads/default_{$gender}.jpg";
    }
    echo "<img src='" . $imgSrc . "' class='profile-img rounded-circle mb-3' alt='" .
         htmlspecialchars($user, ENT_QUOTES, 'UTF-8') . "'>";

    $stmt = $pdo->prepare("SELECT text FROM profiles WHERE user=?");
    $stmt->execute([$user]);
    $row = $stmt->fetch();

    if ($row) {
        echo "<p class='profile-text'>" . htmlspecialchars($row['text'], ENT_QUOTES, 'UTF-8') . "</p>";
    } else {
        echo "<p class='text-muted fst-italic'>Nothing to see here, yet</p>";
    }
}
