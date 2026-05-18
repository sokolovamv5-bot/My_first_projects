<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-messages">
        <?php foreach ($_SESSION['flash'] as $type => $message): ?>
            <div class="flash-message flash-message--<?= htmlspecialchars($type) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
