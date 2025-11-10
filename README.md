# Biostar2 Laravel Package

> A modern Laravel package for seamless interaction with the **Biostar2 API** — enabling elegant user, event, card, and access control management with automatic authentication and caching.

### 1️⃣ Install

```bash
composer require nizami\LaravelBiostar2
````

### 2️⃣ Publish Config

```bash
php artisan vendor:publish --tag=biostar2-config
```

### 3️⃣ Configure `.env`

```env
BIOSTAR2_BASE_URL=https://10.150.20.173
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

### Create User

```php
Biostar2::users()->create([
    'user_id' => '12345',
    'name' => 'John Doe',
    'login_id' => 'johndoe',
    'password' => 'secure_password',
]);
```

### Search Events

```php
use Carbon\Carbon;

$events = Biostar2::events()->search([
    'start_date' => Carbon::now()->startOfMonth(),
    'end_date' => Carbon::now()->endOfMonth(),
    'device_ids' => [544430390],
]);
```

### Assign Card

```php
Biostar2::cards()->createAndAssign('12345', 'CARD-NUMBER-123');
```

---

## 🧠 Key Features

* ✅ Auto token caching and renewal
* ✅ Facade support (`Biostar2::`)
* ✅ Built-in exception handling
* ✅ Configurable devices & event types
* ✅ Elegant, fluent syntax

---

## ⚙️ Configuration

The `config/biostar2.php` file defines connection details, device groups, and event types.

---

## 🧾 License

MIT License

---

> Built for Laravel • Designed for simplicity • Powered by Biostar2
