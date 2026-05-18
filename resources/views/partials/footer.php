<footer class="footer">
    <div class="container">
        <div class="footer__content">
            <div class="footer__section">
                <h4>Дом сказочных узоров</h4>
                <p style="color: var(--color-text-light); font-size: 0.9rem;">
                    Там, где узоры шепчут сказку
                </p>
            </div>
            
            <div class="footer__section">
                <h4>Каталог</h4>
                <ul>
                    <li><a href="/catalog/products">Товары</a></li>
                    <li><a href="/catalog/master-classes">Мастер-классы</a></li>
                </ul>
            </div>
            
            <div class="footer__section">
                <h4>Покупателям</h4>
                <ul>
                    <li><a href="#">Доставка и оплата</a></li>
                    <li><a href="#">Возврат</a></li>
                    <li><a href="#">Контакты</a></li>
                </ul>
            </div>
            
            <div class="footer__section">
                <h4>Личный кабинет</h4>
                <ul>
                    <?php if ($user ?? null): ?>
                        <li><a href="/profile">Профиль</a></li>
                        <li><a href="/my/orders">Заказы</a></li>
                        <li><a href="/my/favorites">Избранное</a></li>
                        <li>
                            <form action="/logout" method="POST" style="display: inline;">
                                <button type="submit" style="background: none; border: none; padding: 0; color: var(--color-text-light); cursor: pointer; font-size: 0.9rem;">Выйти</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li><a href="/login">Войти</a></li>
                        <li><a href="/register">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="footer__bottom">
            <p>&copy; <?= date('Y') ?> Дом сказочных узоров. Все права защищены.</p>
        </div>
    </div>
</footer>
