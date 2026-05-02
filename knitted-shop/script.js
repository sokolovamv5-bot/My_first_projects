// Products Data
const products = [
    {
        id: 1,
        title: "Свитер 'Северное сияние'",
        category: "sweaters",
        categoryName: "Свитера",
        price: 8500,
        description: "Теплый свитер из мериносовой шерсти с красивым узором. Идеально подходит для холодных зимних дней.",
        emoji: "🧥",
        sizes: ["S", "M", "L", "XL"],
        materials: "Мериносовая шерсть 100%"
    },
    {
        id: 2,
        title: "Шарф 'Уютный вечер'",
        category: "scarves",
        categoryName: "Шарфы",
        price: 2500,
        description: "Длинный мягкий шарф крупной вязки. Прекрасно дополнит любой осенний образ.",
        emoji: "🧣",
        sizes: ["One Size"],
        materials: "Альпака 70%, шерсть 30%"
    },
    {
        id: 3,
        title: "Шапка 'Лесная сказка'",
        category: "hats",
        categoryName: "Шапки",
        price: 1800,
        description: "Стильная шапка бини с помпоном. Универсальный размер, подходит всем.",
        emoji: "🧢",
        sizes: ["One Size"],
        materials: "Шерсть 80%, акрил 20%"
    },
    {
        id: 4,
        title: "Плюшевый мишка",
        category: "toys",
        categoryName: "Игрушки",
        price: 3200,
        description: "Очаровательный вязаный мишка ручной работы. Отличный подарок для детей и взрослых.",
        emoji: "🧸",
        sizes: ["30 см"],
        materials: "Хлопок, синтепон"
    },
    {
        id: 5,
        title: "Кардиган 'Бабушкин сад'",
        category: "sweaters",
        categoryName: "Свитера",
        price: 9500,
        description: "Длинный кардиган с цветочным узором. Создан с любовью и вниманием к деталям.",
        emoji: "👘",
        sizes: ["S", "M", "L"],
        materials: "Шерсть 60%, хлопок 40%"
    },
    {
        id: 6,
        title: "Снуд 'Горный воздух'",
        category: "scarves",
        categoryName: "Шарфы",
        price: 2800,
        description: "Объемный снуд-хомут. Очень теплый и удобный, носится в два оборота.",
        emoji: "🌀",
        sizes: ["One Size"],
        materials: "Мохер 50%, шерсть 50%"
    },
    {
        id: 7,
        title: "Шапка с ушками",
        category: "hats",
        categoryName: "Шапки",
        price: 2100,
        description: "Забавная шапка с кошачьими ушками. Поднимет настроение в любую погоду!",
        emoji: "🐱",
        sizes: ["S/M", "L/XL"],
        materials: "Акрил премиум"
    },
    {
        id: 8,
        title: "Вязаный заяц",
        category: "toys",
        categoryName: "Игрушки",
        price: 2900,
        description: "Нежный зайчик с длинными ушками. Безопасные материалы, подходит для детей от 3 лет.",
        emoji: "🐰",
        sizes: ["25 см"],
        materials: "Хлопок, безопасные глаза"
    },
    {
        id: 9,
        title: "Пуловер 'Морской бриз'",
        category: "sweaters",
        categoryName: "Свитера",
        price: 7200,
        description: "Легкий пуловер в морском стиле. Отлично подойдет для прохладных летних вечеров.",
        emoji: "👕",
        sizes: ["XS", "S", "M", "L"],
        materials: "Хлопок 100%"
    },
    {
        id: 10,
        title: "Палантин 'Зимняя вишня'",
        category: "scarves",
        categoryName: "Шарфы",
        price: 3500,
        description: "Элегантный палантин с ажурным узором. Роскошный аксессуар для особых случаев.",
        emoji: "🍒",
        sizes: ["180x50 см"],
        materials: "Кашемир 30%, шерсть 70%"
    },
    {
        id: 11,
        title: "Берет 'Париж'",
        category: "hats",
        categoryName: "Шапки",
        price: 2400,
        description: "Классический французский берет. Добавит элегантности вашему образу.",
        emoji: "🎨",
        sizes: ["One Size"],
        materials: "Шерсть мериноса"
    },
    {
        id: 12,
        title: "Совушка-подушка",
        category: "toys",
        categoryName: "Игрушки",
        price: 2600,
        description: "Вязаная сова, которая может быть как игрушкой, так и декоративной подушкой.",
        emoji: "🦉",
        sizes: ["35 см"],
        materials: "Хлопок, холлофайбер"
    }
];

// Cart State
let cart = [];

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    renderProducts(products);
    setupFilters();
    setupContactForm();
    loadCartFromStorage();
});

// Render Products
function renderProducts(productsToRender) {
    const container = document.getElementById('products-container');
    container.innerHTML = '';

    productsToRender.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.onclick = (e) => {
            if (!e.target.classList.contains('add-to-cart')) {
                showProductModal(product);
            }
        };

        productCard.innerHTML = `
            <div class="product-image">${product.emoji}</div>
            <div class="product-info">
                <div class="product-category">${product.categoryName}</div>
                <h3 class="product-title">${product.title}</h3>
                <p class="product-description">${product.description.substring(0, 80)}...</p>
                <div class="product-footer">
                    <span class="product-price">${formatPrice(product.price)}</span>
                    <button class="add-to-cart" onclick="addToCart(${product.id})">В корзину</button>
                </div>
            </div>
        `;

        container.appendChild(productCard);
    });
}

// Setup Filters
function setupFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            const filter = this.dataset.filter;
            
            if (filter === 'all') {
                renderProducts(products);
            } else {
                const filtered = products.filter(p => p.category === filter);
                renderProducts(filtered);
            }
        });
    });
}

// Format Price
function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

// Add to Cart
function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    
    if (product) {
        const existingItem = cart.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({
                ...product,
                quantity: 1
            });
        }

        updateCartCount();
        saveCartToStorage();
        
        // Show feedback
        showNotification('Товар добавлен в корзину!');
    }
}

// Update Cart Count
function updateCartCount() {
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    document.getElementById('cart-count').textContent = count;
}

// Toggle Cart Modal
function toggleCart() {
    const modal = document.getElementById('cart-modal');
    modal.classList.toggle('active');
    
    if (modal.classList.contains('active')) {
        renderCartItems();
    }
}

// Render Cart Items
function renderCartItems() {
    const container = document.getElementById('cart-items');
    const totalElement = document.getElementById('cart-total');
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="empty-cart">Корзина пуста</p>';
        totalElement.textContent = '0 ₽';
        return;
    }

    let html = '';
    let total = 0;

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;

        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <div class="cart-item-title">${item.title}</div>
                    <div class="cart-item-price">${item.quantity} x ${formatPrice(item.price)}</div>
                </div>
                <button class="cart-item-remove" onclick="removeFromCart(${index})">✕</button>
            </div>
        `;
    });

    container.innerHTML = html;
    totalElement.textContent = formatPrice(total);
}

// Remove from Cart
function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartCount();
    renderCartItems();
    saveCartToStorage();
}

// Checkout
function checkout() {
    if (cart.length === 0) {
        showNotification('Корзина пуста!', 'error');
        return;
    }

    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    let message = 'Здравствуйте! Хочу оформить заказ:\n\n';
    cart.forEach(item => {
        message += `${item.title} - ${item.quantity} шт. (${formatPrice(item.price * item.quantity)})\n`;
    });
    message += `\nИтого: ${formatPrice(total)}`;

    // In a real app, this would send to a server or open WhatsApp/Telegram
    alert(message + '\n\nВ демо-режиме заказ не отправляется.');
    
    // Clear cart
    cart = [];
    updateCartCount();
    saveCartToStorage();
    toggleCart();
    showNotification('Заказ оформлен! Мы свяжемся с вами.');
}

// Show Product Modal
function showProductModal(product) {
    const modal = document.getElementById('product-modal');
    const title = document.getElementById('product-modal-title');
    const body = document.getElementById('product-modal-body');

    title.textContent = product.title;
    
    body.innerHTML = `
        <div class="product-modal-details">
            <div class="product-modal-image">${product.emoji}</div>
            <div class="product-modal-info">
                <h4>${product.title}</h4>
                <div class="product-modal-price">${formatPrice(product.price)}</div>
                <p class="product-modal-description">${product.description}</p>
                <p><strong>Материалы:</strong> ${product.materials}</p>
                <p><strong>Размеры:</strong> ${product.sizes.join(', ')}</p>
                <button class="btn-primary btn-full" style="margin-top: 1rem;" onclick="addToCart(${product.id}); closeProductModal();">Добавить в корзину</button>
            </div>
        </div>
    `;

    modal.classList.add('active');
}

// Close Product Modal
function closeProductModal() {
    const modal = document.getElementById('product-modal');
    modal.classList.remove('active');
}

// Setup Contact Form
function setupContactForm() {
    const form = document.getElementById('contact-form');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // In a real app, this would send to a server
        console.log('Form submitted:', data);
        
        showNotification('Спасибо! Ваша заявка отправлена.');
        form.reset();
    });
}

// Save Cart to LocalStorage
function saveCartToStorage() {
    localStorage.setItem('knittedShopCart', JSON.stringify(cart));
}

// Load Cart from LocalStorage
function loadCartFromStorage() {
    const saved = localStorage.getItem('knittedShopCart');
    if (saved) {
        cart = JSON.parse(saved);
        updateCartCount();
    }
}

// Show Notification
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 3000;
        animation: slideIn 0.3s ease-out;
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Close modals on outside click
window.addEventListener('click', function(e) {
    const cartModal = document.getElementById('cart-modal');
    const productModal = document.getElementById('product-modal');
    
    if (e.target === cartModal) {
        cartModal.classList.remove('active');
    }
    if (e.target === productModal) {
        productModal.classList.remove('active');
    }
});

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
