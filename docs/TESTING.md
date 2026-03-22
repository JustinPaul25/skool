# SFAMS Testing Guide

## Current Status
Phase 2.1 is partially complete. Filament v5.4.1 has a completely different API from v4, requiring adjustment of the implementation.

## What's Been Completed

### 1. User Model ✅
- `canAccessPanel()` method implemented
- FilamentUser interface added
- Role-based access control (administrator, staff, branch_manager can access panel)
- Students are restricted from admin panel

### 2. UserPolicy ✅
- Only administrators can manage users
- Self-deletion prevention
- Auto-discovered by Laravel

### 3. Admin Panel Configuration ✅
- Brand name: "SFAMS"
- Primary color: Amber
- Navigation groups defined
- Login page enabled

## Testing the Current Setup

### 1. Test Database Connection

```bash
php artisan tinker
```

Then run:
```php
// Check users
\App\Models\User::with('roles')->get()

// Check roles
\Spatie\Permission\Models\Role::all()

// Check if admin user exists
\App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'administrator');
})->first()
```

### 2. Test Panel Access

Visit: `http://localhost/admin` (or your configured URL)

You should see the Filament login page.

### 3. Create Test Users

```bash
php artisan tinker
```

```php
// Create administrator
$admin = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@sfams.test',
    'password' => bcrypt('password'),
]);
$admin->assignRole('administrator');

// Create staff member
$staff = \App\Models\User::create([
    'name' => 'Staff Member',
    'email' => 'staff@sfams.test',
    'password' => bcrypt('password'),
]);
$staff->assignRole('staff');

// Create student (should NOT access panel)
$student = \App\Models\User::create([
    'name' => 'Student User',
    'email' => 'student@sfams.test',
    'password' => bcrypt('password'),
]);
$student->assignRole('student');
```

### 4. Test Panel Access Control

1. **Login as Administrator**
   - Email: `admin@sfams.test`
   - Password: `password`
   - ✅ Should be able to access `/admin`

2. **Login as Staff**
   - Email: `staff@sfams.test`
   - Password: `password`
   - ✅ Should be able to access `/admin`

3. **Login as Student**
   - Email: `student@sfams.test`
   - Password: `password`
   - ❌ Should be DENIED access to `/admin`

### 5. Test Landing Page

Visit: `http://localhost` (root URL)

You should see the modern SFAMS landing page with:
- Hero section
- Features section (6 features)
- Benefits section
- Modules section
- CTA section
- Footer

### 6. Run Automated Tests

```bash
# Run all tests
php artisan test

# Or using Pest directly
vendor/bin/pest

# Run with coverage (if needed)
vendor/bin/pest --coverage
```

## Next Steps for Phase 2.1

Since Filament v5 has a different API, we need to:

1. **Research Filament v5 Resource API**
   - Check official documentation
   - Review the Schema-based approach
   - Update UserResource for v5 compatibility

2. **Alternative: Downgrade to Filament v4**
   ```bash
   composer require "filament/filament:^4.0"
   ```

3. **Create Filament v5 Compatible UserResource**
   - Use `Schemas` instead of `Forms`
   - Update table definitions
   - Use v5 component syntax

## Database Queries for Testing

```sql
-- Check all users with their roles
SELECT u.id, u.name, u.email, GROUP_CONCAT(r.name) as roles 
FROM users u 
LEFT JOIN model_has_roles mr ON u.id = mr.model_id 
LEFT JOIN roles r ON mr.role_id = r.id 
GROUP BY u.id, u.name, u.email;

-- Check role permissions
SELECT r.name as role, p.name as permission 
FROM roles r
LEFT JOIN role_has_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id
ORDER BY r.name, p.name;
```

## Troubleshooting

### Issue: "Type of UserResource::$navigationGroup must be UnitEnum|string|null"
**Cause**: Filament v5 API changes
**Solution**: Use methods instead of properties, or wait for updated resource implementation

### Issue: Can't access admin panel
**Checks**:
1. User has correct role: `$user->hasRole('administrator')`
2. User can access panel: `$user->canAccessPanel($panel)`
3. Cache is cleared: `php artisan optimize:clear`

### Issue: Landing page not loading
**Checks**:
1. Assets compiled: `npm run build`
2. Routes working: `php artisan route:list`
3. Check browser console for JS errors

## Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# List all routes
php artisan route:list

# List all Filament resources (once working)
php artisan filament:list

# Run code style fixer
vendor/bin/pint

# Run tests
php artisan test

# Build frontend assets
npm run build

# Dev server with hot reload
npm run dev
```

## Contact

If you encounter issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Browser console
3. Network tab in DevTools
4. PHP error logs

---

*Last Updated: Phase 2.1 - Awaiting Filament v5 resource implementation*
