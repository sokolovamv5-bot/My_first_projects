<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Дом сказочных узоров') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/main.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <main class="main">
        <div class="container">
            <?php include __DIR__ . '/partials/flash_messages.php'; ?>
            
            <?= $content ?? '' ?>
        </div>
    </main>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
    
    <script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
