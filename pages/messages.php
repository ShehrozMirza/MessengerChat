<?php
require_once __DIR__ . '/../includes/header.php';

if (!$loggedin) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$view   = isset($_GET['view']) ? sanitizeString($_GET['view']) : $user;
$lastPm = isset($_GET['pm']) && $_GET['pm'] === '1' ? '1' : '0';
$sendTo = '';

if (isset($_POST['sendTo'])) {
    $sendTo = sanitizeString($_POST['sendTo']);
    if ($sendTo !== '') {
        $view = $sendTo;
    }
}

if (isset($_POST['text']) || isset($_FILES['image']) || isset($_FILES['audio'])) {
    $text = isset($_POST['text']) ? sanitizeString($_POST['text']) : '';
    $pm   = isset($_POST['pm']) ? substr(sanitizeString($_POST['pm']), 0, 1) : '0';
    $imgFile = null;
    $audFile = null;

    $uploadDir = ROOT_DIR . '/uploads/messages/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $uid = uniqid('', true);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/gif','image/jpeg','image/pjpeg','image/png','image/webp'];
        if (in_array($_FILES['image']['type'], $allowed)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?: 'jpg';
            $imgFile = $uid . '.' . preg_replace('/[^a-z0-9]/i', '', $ext);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imgFile);
        }
    }

    if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
        if (in_array($_FILES['audio']['type'], ['audio/webm', 'audio/ogg', 'video/webm'])) {
            $audFile = $uid . '.webm';
            move_uploaded_file($_FILES['audio']['tmp_name'], $uploadDir . $audFile);
        }
    }

    if ($text !== '' || $imgFile || $audFile) {
        $time = (int)time();
        queryMysql("INSERT INTO messages (auth, recip, pm, time, message, image, audio) VALUES(?, ?, ?, ?, ?, ?, ?)",
            [$user, $view, $pm, $time, $text, $imgFile, $audFile]);
    }

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo 'ok';
        exit;
    }
    header("Location: " . BASE_URL . "/pages/messages.php?view=" . urlencode($view) . "&pm=$pm&r=$randstr");
    exit;
}

if (isset($_GET['erase'])) {
    $erase = (int)$_GET['erase'];
    queryMysql("DELETE FROM messages WHERE id=? AND recip=?", [$erase, $user]);
    header("Location: " . BASE_URL . "/pages/messages.php?view=" . urlencode($view) . "&r=$randstr");
    exit;
}

if ($view === $user) {
    $name1 = $name2 = 'Your';
} else {
    $viewSafe = htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
    $name1 = '<a href="' . BASE_URL . '/pages/members.php?view=' . urlencode($view) . '&r=' . $randstr . '">' . $viewSafe . '</a>\'s';
    $name2 = "$viewSafe's";
}

date_default_timezone_set('UTC');
?>
    <h2 class="section-title"><?= $name2 ?> Messages</h2>

    <div class="card mb-4">
        <div class="card-body p-4">
            <?php showProfile($view); ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body p-4">
            <h5 class="mb-3 fw-semibold">
<?php if ($view === $user): ?>
                Send a Message
<?php else: ?>
                Leave a Message for <?= $viewSafe ?>
<?php endif; ?>
            </h5>
            <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/pages/messages.php?view=<?= urlencode($view) ?>&r=<?= $randstr ?>" id="msgForm">
                <input type="hidden" name="sendTo" id="sendToField" value="<?= htmlspecialchars($sendTo, ENT_QUOTES, 'UTF-8') ?>">
<?php if ($view === $user):
    $memberList = queryMysql("SELECT user FROM members WHERE user!=? ORDER BY user", [$user])->fetchAll();
    if ($memberList):
?>
                <div class="mb-3">
                    <label for="sendTo" class="form-label fw-semibold">To</label>
                    <select class="form-select" id="sendTo" onchange="document.getElementById('sendToField').value=this.value; document.getElementById('msgForm').action='<?= BASE_URL ?>/pages/messages.php?view='+encodeURIComponent(this.value)+'&r=<?= $randstr ?>'">
                        <option value="" disabled selected>Select a recipient...</option>
<?php foreach ($memberList as $m): ?>
                        <option value="<?= htmlspecialchars($m['user'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($m['user'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
<?php endforeach; ?>
                    </select>
                </div>
<?php endif; endif; ?>
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="pm" id="public" value="0"<?= $lastPm === '0' ? ' checked' : '' ?>>
                        <label class="btn btn-outline-primary" for="public">
                            <i class="bi bi-globe"></i> Public
                        </label>
                        <input type="radio" class="btn-check" name="pm" id="private" value="1"<?= $lastPm === '1' ? ' checked' : '' ?>>
                        <label class="btn btn-outline-secondary" for="private">
                            <i class="bi bi-lock"></i> Private
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <textarea class="form-control" name="text" rows="3"
                              placeholder="Write your message..."></textarea>
                </div>
                <div id="imagePreview" class="mb-3 d-none">
                    <div class="attach-preview">
                        <img id="imgThumb" src="" alt="preview">
                        <button type="button" class="btn btn-sm btn-danger attach-remove" onclick="removeImage()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <div id="audioPreview" class="mb-3 d-none">
                    <div class="attach-preview audio-attach">
                        <i class="bi bi-mic-fill text-danger me-2"></i>
                        <audio id="audThumb" controls></audio>
                        <button type="button" class="btn btn-sm btn-danger attach-remove ms-2" onclick="removeAudio()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <input type="file" name="image" id="imageInput" accept="image/*" class="d-none">
                <input type="file" name="audio" id="audioInput" class="d-none">
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Post Message
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('imageInput').click()" title="Attach image">
                        <i class="bi bi-image"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="micBtn" onclick="toggleRecording()" title="Voice message">
                        <i class="bi bi-mic"></i>
                    </button>
                    <span id="recStatus" class="small text-danger d-none">
                        <i class="bi bi-circle-fill blink"></i> Recording...
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div class="chat-window" id="chatWindow">
    <div id="messagesList">
<?php
if ($view === $user) {
    $result = queryMysql("SELECT * FROM messages WHERE recip=? OR auth=? ORDER BY time DESC", [$user, $user]);
} else {
    $result = queryMysql(
        "SELECT * FROM messages WHERE
            (recip=? AND pm='0')
            OR (pm!='0' AND auth=? AND recip=?)
            OR (pm!='0' AND auth=? AND recip=?)
        ORDER BY time DESC",
        [$view, $user, $view, $view, $user]
    );
}
$rows  = [];
while ($row = $result->fetch()) {
    if ($row['pm'] === '0' || $row['auth'] === $user || $row['recip'] === $user)
        $rows[] = $row;
}
$shown = count($rows);

// Find the ID of the most recent message sent by the logged-in user (rows are DESC)
$lastMineId = null;
foreach ($rows as $r) {
    if ($r['auth'] === $user) { $lastMineId = (int)$r['id']; break; }
}

$today = date('Y-m-d');

if ($shown > 0):
    foreach ($rows as $row):
        $isMine    = ($row['auth'] === $user);
        $isWhisper = ($row['pm'] != 0);
        $authSafe  = htmlspecialchars($row['auth'], ENT_QUOTES, 'UTF-8');
        $recipSafe = htmlspecialchars($row['recip'], ENT_QUOTES, 'UTF-8');
        $msgDay    = date('Y-m-d', $row['time']);
        $timeStr   = $msgDay === $today
            ? date('g:i a', $row['time'])
            : date('M j, g:i a', $row['time']);
?>
    <div class="chat-row <?= $isMine ? 'chat-sent' : 'chat-received' ?>">
        <div class="chat-bubble <?= $isMine ? 'bubble-sent' : 'bubble-received' ?><?= $isWhisper ? ' bubble-private' : '' ?>">
<?php if (!$isMine): ?>
            <div class="bubble-name"><?= $authSafe ?></div>
<?php elseif ($view === $user && $row['recip'] !== $user): ?>
            <div class="bubble-name">To <?= $recipSafe ?></div>
<?php endif; ?>
<?php if (!empty($row['message'])): ?>
            <div class="bubble-text"><?= htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($row['image'])): ?>
            <div class="msg-image">
                <img src="<?= BASE_URL ?>/uploads/messages/<?= htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8') ?>" alt="image" loading="lazy">
            </div>
<?php endif; ?>
<?php if (!empty($row['audio'])): ?>
            <div class="msg-audio">
                <audio controls preload="none">
                    <source src="<?= BASE_URL ?>/uploads/messages/<?= htmlspecialchars($row['audio'], ENT_QUOTES, 'UTF-8') ?>" type="audio/webm">
                </audio>
            </div>
<?php endif; ?>
            <div class="bubble-footer">
                <span class="bubble-time"><?= $timeStr ?></span>
<?php if (!empty($row['edited'])): ?>
                <span class="bubble-edited">edited</span>
<?php endif; ?>
<?php if ($isWhisper): ?>
                <i class="bi bi-lock-fill bubble-lock"></i>
<?php endif; ?>
<?php if ($isMine): ?>
                <i class="bi bi-check2-all bubble-check"></i>
<?php endif; ?>
<?php if ((int)$row['id'] === $lastMineId && !empty($row['message'])): ?>
                <button type="button" class="bubble-edit" title="Edit"
                        onclick="openEditModal(<?= (int)$row['id'] ?>, <?= json_encode($row['message']) ?>)">
                    <i class="bi bi-pencil"></i>
                </button>
<?php endif; ?>
<?php if ($row['recip'] === $user): ?>
                <a href="<?= BASE_URL ?>/pages/messages.php?view=<?= urlencode($view) ?>&erase=<?= (int)$row['id'] ?>&r=<?= $randstr ?>"
                   class="bubble-delete" title="Delete"><i class="bi bi-trash"></i></a>
<?php endif; ?>
            </div>
        </div>
    </div>
<?php
    endforeach;
endif;

if ($shown === 0):
?>
    <div class="empty-state">
        <i class="bi bi-chat-dots d-block"></i>
        <h5>No messages yet</h5>
        <p class="text-muted">Start the conversation!</p>
    </div>
<?php endif; ?>
    </div>
    </div>

<!-- Edit Message Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="editModalLabel">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Message
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea class="form-control" id="editText" rows="4" placeholder="Message text..."></textarea>
                <div id="editError" class="text-danger small mt-2 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveEdit()">
                    <i class="bi bi-check2 me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function(){
        var baseUrl = '<?= BASE_URL ?>';
        var mediaRecorder = null;
        var audioChunks = [];
        var recording = false;
        var audioBlob = null;

        var chatWin = document.getElementById('chatWindow');
        function scrollBottom(){ chatWin.scrollTop = chatWin.scrollHeight; }
        scrollBottom();

        function refreshMessages(){
            var pm = document.querySelector('input[name="pm"]:checked');
            var pmVal = pm ? pm.value : '0';
            var viewUrl = baseUrl + "/pages/messages.php?view=<?= urlencode($view) ?>&pm=" + pmVal + "&r=" + Date.now().toString(36);
            var atBottom = chatWin.scrollTop + chatWin.clientHeight >= chatWin.scrollHeight - 50;
            fetch(viewUrl)
                .then(function(r){ return r.text(); })
                .then(function(html){
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var fresh = doc.getElementById('messagesList');
                    if (fresh) {
                        document.getElementById('messagesList').innerHTML = fresh.innerHTML;
                        if (atBottom) scrollBottom();
                    }
                });
        }

        setInterval(refreshMessages, 3000);

        document.getElementById('imageInput').addEventListener('change', function(){
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function(e){
                document.getElementById('imgThumb').src = e.target.result;
                document.getElementById('imagePreview').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });

        window.removeImage = function(){
            document.getElementById('imageInput').value = '';
            document.getElementById('imagePreview').classList.add('d-none');
        };

        window.removeAudio = function(){
            audioBlob = null;
            document.getElementById('audioInput').value = '';
            document.getElementById('audioPreview').classList.add('d-none');
        };

        window.toggleRecording = function(){
            if (recording) {
                mediaRecorder.stop();
                return;
            }
            navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream){
                audioChunks = [];
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.ondataavailable = function(e){ audioChunks.push(e.data); };
                mediaRecorder.onstop = function(){
                    stream.getTracks().forEach(function(t){ t.stop(); });
                    audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    var url = URL.createObjectURL(audioBlob);
                    document.getElementById('audThumb').src = url;
                    document.getElementById('audioPreview').classList.remove('d-none');
                    document.getElementById('micBtn').classList.remove('btn-danger');
                    document.getElementById('micBtn').classList.add('btn-outline-secondary');
                    document.getElementById('micBtn').innerHTML = '<i class="bi bi-mic"></i>';
                    document.getElementById('recStatus').classList.add('d-none');
                    recording = false;
                };
                mediaRecorder.start();
                recording = true;
                document.getElementById('micBtn').classList.remove('btn-outline-secondary');
                document.getElementById('micBtn').classList.add('btn-danger');
                document.getElementById('micBtn').innerHTML = '<i class="bi bi-stop-fill"></i>';
                document.getElementById('recStatus').classList.remove('d-none');
            }).catch(function(){
                alert('Microphone access denied. Please allow microphone permission.');
            });
        };

        var editMsgId = null;
        var editModal = null;

        window.openEditModal = function(id, text) {
            editMsgId = id;
            document.getElementById('editText').value = text;
            document.getElementById('editError').classList.add('d-none');
            if (!editModal) editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        };

        window.saveEdit = function() {
            var text = document.getElementById('editText').value.trim();
            var errEl = document.getElementById('editError');
            if (!text) {
                errEl.textContent = 'Message cannot be empty.';
                errEl.classList.remove('d-none');
                return;
            }
            var fd = new FormData();
            fd.append('id', editMsgId);
            fd.append('text', text);
            fetch(baseUrl + '/ajax/edit_message.php', { method: 'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if (data.ok) {
                        editModal.hide();
                        refreshMessages();
                    } else {
                        errEl.textContent = data.error || 'Failed to save.';
                        errEl.classList.remove('d-none');
                    }
                })
                .catch(function(){
                    errEl.textContent = 'Network error. Please try again.';
                    errEl.classList.remove('d-none');
                });
        };

        var form = document.getElementById('msgForm');
        if (form) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                var formData = new FormData(form);
                if (audioBlob) {
                    formData.set('audio', audioBlob, 'voice.webm');
                }
                var actionUrl = form.action;
                var pm = document.querySelector('input[name="pm"]:checked');
                var pmVal = pm ? pm.value : '0';
                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(){
                    var match = actionUrl.match(/view=([^&]*)/);
                    var recipient = match ? decodeURIComponent(match[1]) : '';
                    if (recipient && recipient !== <?= json_encode($user) ?>) {
                        window.location.href = baseUrl + '/pages/messages.php?view=' + encodeURIComponent(recipient) + '&pm=' + pmVal + '&r=' + Date.now().toString(36);
                    } else {
                        form.querySelector('textarea[name="text"]').value = '';
                        removeImage();
                        removeAudio();
                        refreshMessages();
                    }
                });
            });
        }
    })();
    </script>
</body>
</html>
