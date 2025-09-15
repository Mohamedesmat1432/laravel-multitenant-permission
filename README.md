composer config repositories.laravel-multitenant-permission git https://github.com/Mohamedesmat1432/laravel-multitenant-permission.git

composer require esmat/laravel-multitenant-permission

composer update && composer dump-autoload

composer require 

1. Publish Package Assets

# Publish configuration && migrations file
php artisan vendor:publish --provider="Esmat\MultiTenantPermission\Providers\MultiTenantPermissionServiceProvider" --tag=config

php artisan vendor:publish --provider="Esmat\MultiTenantPermission\Providers\MultiTenantPermissionServiceProvider" --tag=migrations


2. Configure Environment 

Update your .env file: 

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_central
DB_USERNAME=root
DB_PASSWORD=

# Cache Configuration (Recommended)
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue Configuration (Recommended for production)
QUEUE_CONNECTION=redis


3. Run Central Database Migrations
php artisan migrate

4. Create Your First Tenant

php artisan tenant:create "Acme Corporation" "acme.example.com"

5. Configure Authentication 

If you haven't already set up authentication: 

# Install Laravel UI or Breeze
composer require laravel/breeze --dev
php artisan breeze:install api

6. Create an Admin User

php artisan tinker


use Esmat\MultiTenantPermission\Models\User;

// Create admin user
$user = User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
]);

// Assign admin role
$user->assignRole('admin');

exit

7. Test the Setup 
Start the Development Server 

php artisan serve


8. Configure Routes 

Update your routes/api.php: 

use Illuminate\Support\Facades\Route;
use Esmat\MultiTenantPermission\Http\Controllers\UserController;

// Public routes
Route::post('/login', 'AuthController@login');

// Protected routes
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    // User management
    Route::apiResource('users', UserController::class)
         ->middleware('permission:view-users');
    
    // Add your other routes here
});


9. Customize Configuration 

Edit config/multitenant-permission.php: 


return [
    // Configure tenant identification method
    'tenant_identification' => [
        'header' => 'X-Tenant-ID',
        'domain' => true, // Enable domain-based identification
        'subdomain' => false,
    ],
    
    // Configure default roles and permissions
    'default_roles' => [
        'super-admin' => [
            'permissions' => ['*'],
            'description' => 'Super Administrator'
        ],
        // Add or modify roles as needed
    ],
    
    // Configure caching
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'multitenant:',
    ],
];


10. Set Up Queue Worker (Recommended for Production)

# Start the queue worker
php artisan queue:work

# Or use Horizon if installed
php artisan horizon

11. Create Additional Tenants

# Create more tenants as needed
php artisan tenant:create "Another Company" "another.example.com"
php artisan tenant:create "Third Company" "third.example.com"

2. Implement Tenant-Specific Features 

Create tenant-specific controllers, models, and features: 

// app/Http/Controllers/Tenant/ProductController.php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Esmat\MultiTenantPermission\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);
        
        return Product::create($request->all());
    }
}

13. Set Up Monitoring
Add monitoring for tenant-specific activities: 

// app/Providers/AppServiceProvider.php
public function boot()
{
    // Monitor tenant creation
    \Esmat\MultiTenantPermission\Models\Tenant::created(function ($tenant) {
        \Log::info("New tenant created: {$tenant->name}");
    });
    
    // Monitor user creation
    \Esmat\MultiTenantPermission\Models\User::created(function ($user) {
        \Log::info("New user created in tenant: {$user->tenant->name}");
    });
}

14. Create Documentation 

Document your multi-tenant setup for future developers: 


# Multi-Tenant Application Setup

## Creating a New Tenant
1. Run: `php artisan tenant:create "Company Name" "domain.example.com"`
2. Update DNS settings to point domain to your server
3. Configure web server for the new domain

## Adding New Permissions
1. Add permission to database
2. Assign to appropriate roles
3. Update route middleware

## Tenant Isolation
- All models must use tenant-specific database connections
- Always verify tenant context in queries


15. Implement Backup Strategy 

Set up backups for both central and tenant databases: 


# Install backup package
composer require spatie/laravel-backup

# Publish config
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# Configure backup in config/backup.php



16. Performance Optimization 
Implement Caching 


// Cache tenant configurations
$tenant = \Esmat\MultiTenantPermission\Facades\Tenant::current();
$settings = Cache::remember("tenant_{$tenant->id}_settings", 3600, function () use ($tenant) {
    return $tenant->settings;
});

Optimize Database Queries

// Use eager loading to avoid N+1 queries
$users = User::with('roles', 'permissions')->get();



17. Security Enhancements 
Implement Rate Limiting 

// In routes/api.php
Route::middleware(['throttle:api'])->group(function () {
    Route::post('/login', 'AuthController@login');
});

// In controllers Validate Tenant Access
public function show($id)
{
    $user = User::findOrFail($id);
    
    // Ensure user belongs to current tenant
    if ($user->tenant_id !== \Esmat\MultiTenantPermission\Facades\Tenant::current()->id) {
        abort(403, 'Unauthorized access');
    }
    
    return $user;
}


18. Testing 
Create Feature Tests 

// tests/Feature/TenantTest.php
public function test_tenant_creation()
{
    $response = $this->postJson('/api/tenants', [
        'name' => 'Test Tenant',
        'domain' => 'test.example.com',
    ]);
    
    $response->assertStatus(201);
    $this->assertDatabaseHas('tenants', ['name' => 'Test Tenant']);
}

php artisan test

19. Deployment Preparation 
Optimize for Production 
bash
 
php artisan config:cache
php artisan route:cache
php artisan view:cache

// Set Environment Variables
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stack


20. Maintenance Commands

php artisan make:command TenantMaintenance

// app/Console/Commands/TenantMaintenance.php
class TenantMaintenance extends Command
{
    protected $signature = 'tenant:maintenance {action}';
    
    public function handle()
    {
        $action = $this->argument('action');
        
        if ($action === 'cleanup') {
            // Clean up old data
        }
        
        $this->info("Maintenance completed");
    }
}

