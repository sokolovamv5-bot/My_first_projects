// Main JavaScript for "Дом сказочных узоров"

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sliders with smooth scrolling
    initSliders();
    
    // Initialize tabs for master class pages
    initTabs();
    
    // Initialize product gallery
    initProductGallery();
    
    // Initialize cart functionality
    initCart();
    
    // Initialize favorites
    initFavorites();
    
    // Auto-hide flash messages
    initFlashMessages();
});

/**
 * Initialize horizontal sliders with touch support
 */
function initSliders() {
    const containers = document.querySelectorAll('.slider__container');
    
    containers.forEach(container => {
        let isDown = false;
        let startX;
        let scrollLeft;
        
        container.addEventListener('mousedown', (e) => {
            isDown = true;
            container.classList.add('active');
            startX = e.pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
        });
        
        container.addEventListener('mouseleave', () => {
            isDown = false;
            container.classList.remove('active');
        });
        
        container.addEventListener('mouseup', () => {
            isDown = false;
            container.classList.remove('active');
        });
        
        container.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 2;
            container.scrollLeft = scrollLeft - walk;
        });
    });
}

/**
 * Initialize tab functionality for master class pages
 */
function initTabs() {
    const tabButtons = document.querySelectorAll('.mc-detail__tab');
    const tabContents = document.querySelectorAll('.mc-detail__tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.dataset.tab;
            
            // Remove active class from all tabs and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            button.classList.add('active');
            const targetContent = document.querySelector(`.mc-detail__tab-content[data-tab="${targetTab}"]`);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
}

/**
 * Initialize product gallery with thumbnail selection
 */
function initProductGallery() {
    const thumbnails = document.querySelectorAll('.product-detail__thumbnail');
    const mainImage = document.querySelector('.product-detail__main-image');
    
    if (!mainImage || thumbnails.length === 0) return;
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', () => {
            // Update main image
            mainImage.src = thumbnail.src;
            
            // Update active state
            thumbnails.forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        });
    });
}

/**
 * Initialize cart functionality (AJAX add/remove/update)
 */
function initCart() {
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('[data-add-to-cart]');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const productId = button.dataset.addToCart;
            const quantity = button.dataset.quantity || 1;
            
            try {
                const response = await fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=${quantity}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    updateCartCount(result.cartCount);
                    showFlashMessage('success', 'Товар добавлен в корзину');
                } else {
                    showFlashMessage('error', result.message || 'Ошибка при добавлении в корзину');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                showFlashMessage('error', 'Произошла ошибка при добавлении в корзину');
            }
        });
    });
    
    // Cart quantity update
    const quantityInputs = document.querySelectorAll('.cart-table__quantity-input');
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', async () => {
            const itemId = input.dataset.itemId;
            const quantity = input.value;
            
            try {
                const response = await fetch('/cart/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `item_id=${itemId}&quantity=${quantity}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    updateCartTotal(result.total);
                    updateCartCount(result.cartCount);
                }
            } catch (error) {
                console.error('Error updating cart:', error);
            }
        });
    });
    
    // Cart item removal
    const removeButtons = document.querySelectorAll('[data-remove-from-cart]');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const itemId = button.dataset.removeFromCart;
            
            try {
                const response = await fetch('/cart/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `item_id=${itemId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    button.closest('tr').remove();
                    updateCartTotal(result.total);
                    updateCartCount(result.cartCount);
                    
                    if (result.cartCount === 0) {
                        location.reload();
                    }
                }
            } catch (error) {
                console.error('Error removing from cart:', error);
            }
        });
    });
}

/**
 * Initialize favorites functionality
 */
function initFavorites() {
    const favoriteButtons = document.querySelectorAll('[data-toggle-favorite]');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const itemType = button.dataset.toggleFavorite.split(':')[0];
            const itemId = button.dataset.toggleFavorite.split(':')[1];
            
            try {
                const response = await fetch('/favorites/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `type=${itemType}&item_id=${itemId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    button.classList.toggle('active');
                    button.textContent = result.isFavorite ? '♥ В избранном' : '♡ В избранное';
                    showFlashMessage('success', result.message);
                }
            } catch (error) {
                console.error('Error toggling favorite:', error);
            }
        });
    });
}

/**
 * Update cart count in header
 */
function updateCartCount(count) {
    const cartCountEl = document.querySelector('.header__cart-count');
    
    if (count > 0) {
        if (cartCountEl) {
            cartCountEl.textContent = count;
        } else {
            const cartIcon = document.querySelector('.header__cart-icon');
            if (cartIcon) {
                const span = document.createElement('span');
                span.className = 'header__cart-count';
                span.textContent = count;
                cartIcon.appendChild(span);
            }
        }
    } else if (cartCountEl) {
        cartCountEl.remove();
    }
}

/**
 * Update cart total
 */
function updateCartTotal(total) {
    const totalEl = document.querySelector('.cart-summary__total-value');
    
    if (totalEl && total !== undefined) {
        totalEl.textContent = total.toLocaleString('ru-RU') + ' ₽';
    }
}

/**
 * Show flash message
 */
function showFlashMessage(type, message) {
    const container = document.querySelector('.flash-messages') || createFlashContainer();
    
    const messageEl = document.createElement('div');
    messageEl.className = `flash-message flash-message--${type}`;
    messageEl.textContent = message;
    
    container.appendChild(messageEl);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageEl.style.opacity = '0';
        setTimeout(() => messageEl.remove(), 300);
    }, 5000);
}

/**
 * Create flash messages container if it doesn't exist
 */
function createFlashContainer() {
    const container = document.createElement('div');
    container.className = 'flash-messages';
    document.body.appendChild(container);
    return container;
}

/**
 * Auto-hide flash messages
 */
function initFlashMessages() {
    const messages = document.querySelectorAll('.flash-message');
    
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
}

/**
 * Form validation helper
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    
    if (!form) return false;
    
    form.addEventListener('submit', (e) => {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showFlashMessage('error', 'Пожалуйста, заполните все обязательные поля');
        }
    });
}

/**
 * Image preview for file inputs
 */
function initImagePreview() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            const previewId = input.dataset.preview;
            const previewEl = document.getElementById(previewId);
            
            if (file && previewEl) {
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    previewEl.src = e.target.result;
                    previewEl.style.display = 'block';
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
}
