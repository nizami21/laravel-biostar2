
---

# 🧠 Biostar2 Laravel Package — Usage Examples

This document demonstrates how to use the **Biostar2 Laravel package**.
---

## ⚙️ Setup

```php
use nizami\LaravelBiostar2\Facades\Biostar2;
use Carbon\Carbon;
```

---

## 👤 Example 1: Creating a User (Employee)

```php
class EmployeeController
{
    public function store(EmployeeRequest $request)
    {
        try {
            DB::beginTransaction();

            // Get next user ID from Biostar
            $userId = Biostar2::users()->getNextUserId();

            // Create employee in local database
            $employee = new Employee;
            $employee->id = $userId;
            $employee->fill($request->validated());
            $employee->save();

            // Attach holidays
            if ($request->has('holidays')) {
                $employee->holidays()->attach($request->holidays);
            }

            // Prepare access groups from department
            $accessGroupIds = [];
            if ($employee->department && $employee->department->buildings) {
                $accessGroupIds = $employee->department->buildings
                    ->pluck('access_group')
                    ->flatten(1)
                    ->unique('access_group_id')
                    ->pluck('access_group_id')
                    ->toArray();
            }

            // Create user in Biostar
            $biostarUser = Biostar2::users()->create([
                'user_id' => $userId,
                'name' => $employee->fullname,
                'email' => $employee->id . '@gmail.com',
                'login_id' => $employee->id,
                'password' => 'password',
                'access_groups' => array_map(fn($id) => ['id' => $id], $accessGroupIds),
            ]);

            // Create and assign card if provided
            if ($request->filled('card_number')) {
                Biostar2::cards()->createAndAssign($userId, $request->card_number);
            }

            DB::commit();
            return response()->json(['message' => 'Employee created successfully'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

---

## 🔑 Example 2: Updating Access Groups

```php
class AccessController
{
    public function updateAccessGroups(Request $request, $id)
    {
        try {
            $newAccessGroupIds = $request->input('access_groups', []);
            
            // Extract IDs from nested structure
            $accessGroupIds = [];
            foreach ($newAccessGroupIds as $group) {
                foreach ($group['access_group'] as $access) {
                    $accessGroupIds[] = (int)$access['access_group_id'];
                }
            }

            // Update access groups (merge with existing)
            $result = Biostar2::users()->updateAccessGroups($id, $accessGroupIds);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeAccessGroups(Request $request, $id)
    {
        try {
            $groupsToRemove = $request->input('access_groups_to_remove', []);
            $idsToRemove = array_column($groupsToRemove, 'access_group_id');

            $result = Biostar2::users()->removeAccessGroups($id, $idsToRemove);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

---

## 🔥 Example 3: Searching Events (Smoke Records)

```php
class EventController
{
    public function smokeRecords(Request $request)
    {
        $SMOKE_DEVICES = config('biostar2.devices.smoke', [544430390, 544430379]);
        
        try {
            $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
            $endDate = Carbon::parse($request->input('end_date', now()->endOfMonth()));
            
            // Determine employee IDs based on department access
            $employeeIds = $this->getAccessibleEmployeeIds($request);

            // Search events using the package
            $events = Biostar2::events()->search([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'device_ids' => $SMOKE_DEVICES,
                'user_ids' => $employeeIds,
                'event_types' => [4354, 4102, 6401],
            ]);

            // Return or calculate based on request
            if ($request->boolean('should_calculate')) {
                $report = $this->smokeService->calculate($events, $startDate, $endDate);
                return response()->json($report);
            }

            return SmokeEventResource::collection(collect($events))->response();

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

---

## 📅 Example 4: Today's Events by Device

```php
class DashboardController
{
    public function searchEvents(Request $request)
    {
        try {
            // Get today's access events for a specific device
            $events = Biostar2::events()->searchTodayByDevice(
                $request->device_id,
                [4102] // Access granted events only
            );

            if (empty($events)) {
                return response()->json([]);
            }

            // Extract employee IDs
            $employeeIds = array_map(fn($e) => (int)$e['user_id']['user_id'], $events);

            // Get employees
            $employees = Employee::with('department')
                ->whereIn('id', $employeeIds)
                ->get()
                ->keyBy('id');

            // Format results
            $result = [];
            foreach ($events as $event) {
                $employeeId = (int)$event['user_id']['user_id'];
                $employee = $employees->get($employeeId);

                if (!$employee) continue;

                $result[] = [
                    'datetime' => Carbon::parse($event['server_datetime'])->format('Y-m-d H:i:s'),
                    'employee_name' => $employee->fullname,
                    'employee_id' => $employeeId,
                    'department' => $employee->department->name ?? 'Unknown',
                    'employee_status' => 'დაშვებულია',
                    'device_id' => $event['device_id']['id'],
                    'device_name' => $event['device_id']['name'],
                ];
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

---

## ❌ Example 5: Deleting or Deactivating a User

```php
class EmployeeManagementController
{
    public function destroy(Request $request, Employee $employee)
    {
        try {
            DB::beginTransaction();

            // Deactivate in Biostar
            if ($request->has('expiry_datetime')) {
                $expiryDate = Carbon::parse($request->input('expiry_datetime'));
                Biostar2::users()->deactivate($employee->id, $expiryDate);
            }

            // Remove cards
            if ($employee->card_number) {
                Biostar2::users()->removeCards($employee->id);
                $employee->card_number = null;
            }

            $employee->active = false;
            $employee->save();

            DB::commit();
            return response()->json(['message' => 'Employee deactivated successfully'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeUser(Employee $employee)
    {
        try {
            DB::beginTransaction();

            // Delete from Biostar
            $deleted = Biostar2::users()->delete($employee->id);

            if ($deleted) {
                $employee->delete();
                DB::commit();
                return response()->json(['message' => 'Employee deleted successfully'], 200);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Handle case where user doesn't exist in Biostar
            if (str_contains($e->getMessage(), 'User can not be found')) {
                $employee->delete();
                return response()->json(['message' => 'Employee deleted from local'], 200);
            }

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

---

## 🧩 Example 6: Console Command — Add Access to All

```php
class AddAccessToAll extends Command
{
    protected $signature = 'biostar:add-access-to-all {access_group_id}';
    protected $description = 'Add access group to all employees';

    public function handle()
    {
        $accessGroupId = (int)$this->argument('access_group_id');
        $employees = Employee::all();

        $this->info("Adding access group {$accessGroupId} to {$employees->count()} employees...");
        $bar = $this->output->createProgressBar($employees->count());

        foreach ($employees as $employee) {
            try {
                Biostar2::users()->updateAccessGroups($employee->id, [$accessGroupId]);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("\nFailed for employee {$employee->id}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->info("\nCompleted!");
    }
}
```

---

## 🧱 Example 7: Using Raw API Methods

```php
class CustomController
{
    public function customEndpoint()
    {
        // You can still make raw API calls if needed
        $response = Biostar2::get('/api/custom/endpoint', ['param' => 'value']);
        
        // Or
        $response = Biostar2::post('/api/custom/endpoint', [
            'data' => 'value'
        ]);

        return response()->json($response->json());
    }
}
```

---

## 📚 Summary

| Action               | Method                                          |
| -------------------- | ----------------------------------------------- |
| Create User          | `Biostar2::users()->create()`                   |
| Get Events           | `Biostar2::events()->search()`                  |
| Manage Access Groups | `updateAccessGroups()` / `removeAccessGroups()` |
| Assign Card          | `Biostar2::cards()->createAndAssign()`          |
| Deactivate User      | `Biostar2::users()->deactivate()`               |
| Delete User          | `Biostar2::users()->delete()`                   |

---

Would you like me to format it into a **ready-to-use README.md file** with a proper header, installation instructions, and a short intro section for your package?
