<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_DIR . '/includes/functions.php';

if (isset($_POST['user'])) {
    $user = sanitizeString($_POST['user']);
    $stmt = queryMysql("SELECT user FROM members WHERE user=?", [$user]);

    if ($stmt->rowCount())
        echo '<span class="taken"><i class="bi bi-x-circle"></i> Username &ldquo;' .
             htmlspecialchars($user, ENT_QUOTES, 'UTF-8') . '&rdquo; is taken</span>';
    else
        echo '<span class="available"><i class="bi bi-check-circle"></i> Username &ldquo;' .
             htmlspecialchars($user, ENT_QUOTES, 'UTF-8') . '&rdquo; is available</span>';
}
?>
