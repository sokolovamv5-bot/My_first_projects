# Дом сказочных узоров

Интернет-магазин рукодельных изделий и обучающих мастер-классов.

## Требования

- PHP 8.1+
- MySQL / MariaDB
- Apache с mod_rewrite (или другой веб-сервер с поддержкой URL rewriting)

## Установка

### 1. Клонирование репозитория

```bash
git clone <repository-url>
cd house-of-patterns
```

### 2. Настройка окружения

Скопируйте файл `.env.example` в `.env` и настройте параметры:

```bash
cp .env.example .env
```

Отредактируйте `.env`:

```env
DB_HOST=localhost
DB_NAME=house_of_patterns
DB_USER=root
DB_PASS=your_password

APP_URL=http://localhost
APP_ENV=development
APP_DEBUG=true
```

### 3. Создание базы данных

Импортируйте схему базы данных:

```bash
mysql -u root -p < database/schema.sql
```

Или выполните SQL-файл через phpMyAdmin или другой инструмент.

### 4. Настройка веб-сервера

#### Apache

Убедитесь, что включен mod_rewrite:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Настройте DocumentRoot на папку `public/`:

```apache
<VirtualHost *:80>
    ServerName housepatterns.local
    DocumentRoot /path/to/house-of-patterns/public
    
    <Directory /path/to/house-of-patterns/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

Пример конфигурации:

```nginx
server {
    listen 80;
    server_name housepatterns.local;
    root /path/to/house-of-patterns/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 5. Права доступа

Убедитесь, что веб-сервер имеет права на запись в папки:

```bash
chmod -R 755 storage/
chmod -R 755 var/
chown -R www-data:www-data storage/
chown -R www-data:www-data var/
```

### 6. Создание тестовых данных (опционально)

Для заполнения базы тестовыми данными выполните:

```bash
mysql -u root -p house_of_patterns < database/test_data.sql
```

## Вход для администратора

По умолчанию создан администратор:

- **Email:** admin@housepatterns.ru
- **Пароль:** admin123

**Важно:** Смените пароль после первого входа!

## Структура проекта

```
project-root/
├── public/              # Публичная директория (DocumentRoot)
│   ├── index.php        # Точка входа
│   ├── .htaccess        # Правила перезаписи URL
│   └── assets/          # CSS, JS, изображения
├── app/                 # Исходный код приложения
│   ├── Core/            # Ядро MVC
│   ├── Controllers/     # Контроллеры
│   ├── Models/          # Модели
│   └── Services/        # Сервисы
├── config/              # Конфигурационные файлы
├── resources/views/     # Шаблоны представлений
├── storage/             # Загруженные файлы
├── var/                 # Логи и кэш
└── database/            # SQL-файлы
```

## Маршруты

### Публичные

- `GET /` - Главная страница
- `GET /catalog/products` - Каталог товаров
- `GET /catalog/master-classes` - Каталог мастер-классов
- `GET /product/{id}` - Страница товара
- `GET /master-class/{id}` - Страница мастер-класса
- `GET /login` - Вход
- `GET /register` - Регистрация
- `GET /cart` - Корзина
- `GET /checkout` - Оформление заказа

### Личный кабинет

- `GET /profile` - Профиль
- `GET /my/master-classes` - Мои мастер-классы
- `GET /my/orders` - Мои заказы
- `GET /my/favorites` - Избранное

### Админ-панель

- `GET /admin` - Дашборд
- `GET /admin/products` - Управление товарами
- `GET /admin/master-classes` - Управление мастер-классами
- `GET /admin/orders` - Заказы
- `GET /admin/questions` - Вопросы пользователей

## Особенности

### Режим редактирования контента

Администратор может включить режим редактирования в админ-панели. В этом режиме на публичных страницах появляются иконки редактирования возле текстовых блоков и изображений.

### Защита видео-контента

Видео мастер-классов защищены от прямого скачивания:
- Доступ через специальный контроллер с проверкой прав
- Проверка авторизации пользователя
- Проверка наличия доступа к МК (покупка или активная подписка)

## Разработка

### Добавление нового контроллера

```php
<?php
namespace App\Controllers;

use App\Core\Controller;

class NewController extends Controller
{
    public function index(): void
    {
        $this->view('new/index', [
            'pageTitle' => 'Новая страница',
            'user' => $this->user
        ]);
    }
}
```

### Добавление маршрута

Добавьте маршрут в `config/routes.php`:

```php
'GET /new-page' => 'NewController@index',
```

## Лицензия

Все права защищены © 2024 Дом сказочных узоров
