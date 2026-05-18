<header class="header">
    <div class="container">
        <div class="header__top">
            <a href="/" class="header__logo">
                Дом сказочных узоров
                <span>Там, где узоры шепчут сказку</span>
            </a>
            
            <nav class="header__nav">
                <ul>
                    <li><a href="/catalog/products">Товары</a></li>
                    <li><a href="/catalog/master-classes">Мастер-классы</a></li>
                    <?php if ($user ?? null): ?>
                        <li><a href="/profile">Личный кабинет</a></li>
                    <?php else: ?>
                        <li><a href="/login">Войти</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="header__user">
                <a href="/cart" class="header__cart-icon">
                    🛒
                    <?php
                    $cartCount = 0;
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    if (isset($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) {
                            $cartCount += $item['quantity'] ?? 1;
                        }
                    }
                    if ($cartCount > 0): ?>
                        <span class="header__cart-count"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</header>
