<?php
/**
 * Home Page View
 */
?>

<section class="hero">
    <h1 style="text-align: center; margin-bottom: 2rem; font-size: 2.5rem;">
        Добро пожаловать в Дом сказочных узоров
    </h1>
    
    <div class="hero__sliders">
        <!-- Products Slider -->
        <div class="hero__slider">
            <h2 class="hero__slider-title">Товары</h2>
            <div class="slider__container">
                <div class="slider__track">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <a href="/product/<?= $product['product_id'] ?>" class="slider__card">
                                <img src="<?= htmlspecialchars($product['main_image'] ?? '/assets/images/placeholder.jpg') ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="slider__card-image">
                                <div class="slider__card-content">
                                    <h3 class="slider__card-title"><?= htmlspecialchars($product['name']) ?></h3>
                                    <p class="slider__card-price"><?= number_format($product['price'], 0, '.', ' ') ?> ₽</p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 1rem; color: var(--color-text-light);">Товары скоро появятся</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Master Classes Slider -->
        <div class="hero__slider">
            <h2 class="hero__slider-title">Мастер-классы</h2>
            <div class="slider__container">
                <div class="slider__track">
                    <?php if (!empty($masterClasses)): ?>
                        <?php foreach ($masterClasses as $mc): ?>
                            <a href="/master-class/<?= $mc['mc_id'] ?>" class="slider__card">
                                <img src="/assets/images/placeholder-mc.jpg" 
                                     alt="<?= htmlspecialchars($mc['title']) ?>" 
                                     class="slider__card-image">
                                <div class="slider__card-content">
                                    <h3 class="slider__card-title"><?= htmlspecialchars($mc['title']) ?></h3>
                                    <p style="font-size: 0.85rem; color: var(--color-text-light); margin-bottom: 0.5rem;">
                                        <?= htmlspecialchars($mc['difficulty']) ?> • <?= htmlspecialchars($mc['technique']) ?>
                                    </p>
                                    <p class="slider__card-price"><?= number_format($mc['price_buy'], 0, '.', ' ') ?> ₽</p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 1rem; color: var(--color-text-light);">Мастер-классы скоро появятся</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="hero__cta">
        <a href="/catalog/products" class="btn btn--primary">Выбрать товар</a>
        <a href="/catalog/master-classes" class="btn btn--secondary">Выбрать мастер-класс</a>
    </div>
</section>
