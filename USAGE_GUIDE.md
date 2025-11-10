# Biostar2 Laravel Package — Usage Guide
```markdown

A comprehensive guide on using the Biostar2 Laravel package for full control of your Biostar2 system through Laravel.

---

## 🔐 Authentication

Authentication is automatic.  
You can manually clear or refresh sessions if needed:

```php
Biostar2::clearSession();
Biostar2::authenticate();
````

---

## 👤 User Management

### Create User

```php
$user = Biostar2::users()->create([
    'user_id' => '12345',
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'login_id' => 'johndoe',
    'password' => 'secure_password',
]);
```

### Get / Update / Delete

```php
$user = Biostar2::users()->get('12345');
Biostar2::users()->update('12345', ['name' => 'Jane Doe']);
Biostar2::users()->delete('12345');
```

### Manage Access Groups

```php
Biostar2::users()->updateAccessGroups('12345', [1, 2, 3]);
Biostar2::users()->removeAccessGroups('12345', [2]);
Biostar2::users()->setAccessGroups('12345', [1, 4, 5]);
$groups = Biostar2::users()->getAccessGroups('12345');
```

### Activate / Deactivate

```php
use Carbon\Carbon;

Biostar2::users()->deactivate('12345', Carbon::now());
Biostar2::users()->activate('12345', Carbon::parse('2025-12-31'));
```

---

## 💳 Card Management

### Create and Assign

```php
Biostar2::cards()->createAndAssign('12345', 'CARD-NUMBER-123');
```

### Separate Steps

```php
$card = Biostar2::cards()->create('CARD-NUMBER-123');
Biostar2::cards()->assignToUser('12345', $card['id']);
```

### Remove Cards

```php
Biostar2::users()->removeCards('12345');
```

---

## 📅 Event Searching

### Search with Filters

```php
use Carbon\Carbon;

$events = Biostar2::events()->search([
    'start_date' => Carbon::now()->startOfMonth(),
    'end_date' => Carbon::now()->endOfMonth(),
    'device_ids' => [544430390, 544430379],
    'user_ids' => [1, 2, 3],
    'event_types' => [4102, 4354, 6401],
]);
```

### Group by User and Date

```php
$events = Biostar2::events()->search([...]);
$grouped = Biostar2::events()->groupByDateAndUser($events);
```

---

## 🏢 Access Groups

```php
$groups = Biostar2::accessGroups()->all();
$group = Biostar2::accessGroups()->get('group-id');

Biostar2::accessGroups()->search([
    ['column' => 'name', 'operator' => 0, 'values' => ['Main Building']]
]);
```

---

## 🧾 Raw API Calls

For unsupported endpoints:

```php
$response = Biostar2::get('/api/custom/endpoint', ['param' => 'value']);
$response = Biostar2::post('/api/custom/endpoint', ['data' => 'value']);
$response = Biostar2::put('/api/users/123', ['name' => 'New Name']);
$response = Biostar2::delete('/api/cards/456']);

$data = $response->json();
```

---

## ⚠️ Error Handling

All errors throw a `Biostar2Exception`:

```php
use nizami\LaravelBiostar2\Exceptions\Biostar2Exception;

try {
    Biostar2::users()->create([...]);
} catch (Biostar2Exception $e) {
    Log::error('Biostar2 error: ' . $e->getMessage());
}
```

---

## 🧪 Testing

You can easily mock Biostar2 in tests:

```php
Biostar2::shouldReceive('users->create')
    ->once()
    ->andReturn(['user_id' => '123']);
```

---

## 🧠 Event Type Constants

```php
use nizami\LaravelBiostar2\Resources\EventResource;

EventResource::EVENT_ACCESS_GRANTED; // 4102
EventResource::EVENT_ACCESS_DENIED;  // 4354
EventResource::EVENT_DOOR_OPENED;    // 6401
```

## Operator Constants

```php
EventResource::OPERATOR_EQUAL;   // 0
EventResource::OPERATOR_IN;      // 2
EventResource::OPERATOR_BETWEEN; // 3
```

---

## 💡 Tips

* Configure multiple devices in `config/biostar2.php` for quick lookups
* All methods return Laravel-style collections or arrays
* SSL verification can be toggled per environment

---

## 📘 License

MIT License © nizami

---

> 🧩 Built for Laravel · Simplified Biostar2 Integration
