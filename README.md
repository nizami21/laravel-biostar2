# Biostar2 Laravel Package

> A modern Laravel package for seamless interaction with the **Biostar2 API** — enabling elegant user, event, card, and access control management with automatic authentication and caching.

### 1️⃣ Install

```bash
composer require nizami/laravel-biostar2
````

### 2️⃣ Publish Config

```bash
php artisan vendor:publish --tag=biostar2-config
```

### 3️⃣ Configure `.env`

```env
BIOSTAR2_BASE_URL=https://your-biostar-server.com
BIOSTAR2_LOGIN_ID=your_admin_username
BIOSTAR2_PASSWORD=your_admin_password
BIOSTAR2_VERIFY_SSL=false
BIOSTAR2_TOKEN_CACHE_DURATION=3600
```

## 🧩 Basic Usage

```php
use nizami\LaravelBiostar2\Facades\Biostar2;

// Auto-handles authentication
$userId = Biostar2::users()->getNextUserId();
```

### Door Control

```php
// Unlock a door remotely
Biostar2::doors()->unlock('101');
```

### Search Events

```php
use Carbon\Carbon;

$events = Biostar2::events()->search([
    'start_date' => Carbon::now()->startOfDay(),
    'end_date' => Carbon::now()->endOfDay(),
    'event_types' => [4102], // Access Granted
]);
```

### User Management

```php
Biostar2::users()->create([
    'user_id' => '12345',
    'name' => 'John Doe',
    'login_id' => 'johndoe',
    'password' => 'secure_password',
]);
```

---

## 🧠 Key Features

* ✅ **Auto Authentication**: Token caching and automatic renewal on expiration.
* ✅ **Hardware Control**: Remote unlock/lock/reboot for Doors and Devices.
* ✅ **Comprehensive Resources**: Users, Cards, Events, Access Groups, User Groups, and more.
* ✅ **Logical Error Detection**: Automatically detects hardware-level errors even on `200 OK` responses.
* ✅ **Facade Support**: Clean `Biostar2::` syntax.

---

## ⚙️ Configuration

The `config/biostar2.php` file defines connection details, device mappings, and default event types.

---

## 🧾 License

MIT License