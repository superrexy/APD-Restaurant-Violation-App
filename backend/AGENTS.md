# AGENTS.md - Laravel 12 REST API Development Guide

## TECH STACK

- **Laravel:** 12.x (latest)
- **PHP:** 8.2+
- **Database:** PostgreSQL
- **API Auth:** Sanctum 4.x
- **WebSocket:** Reverb 1.x
- **Testing:** Pest 4.x
- **Formatting:** Laravel Pint (default rules)
- **API Docs:** Scramble (OpenAPI 3.1.0)

## ESSENTIAL COMMANDS

```bash
# Setup
composer setup              # Full project installation
php artisan key:generate    # Generate app key

# Database
php artisan migrate         # Run all migrations
php artisan migrate:fresh   # Fresh database (wipes data)
php artisan migrate:rollback  # Rollback last migration

# Code Generation (ALWAYS USE ARTISAN)
php artisan make:controller UserController --api          # API controller
php artisan make:model User -mcr                          # Model + migration + controller
php artisan make:migration create_table_name_table        # Create migration
php artisan make:request UserStoreRequest                 # FormRequest for store
php artisan make:request UserUpdateRequest                # FormRequest for update

# Testing
php artisan test                          # Run all tests
php artisan test --filter testMethodName   # Run single test
./vendor/bin/pest                         # Alternative Pest runner
./vendor/bin/pest tests/Feature/UserTest.php  # Run specific test file

# Server & Queue
php artisan serve                         # Start dev server (http://localhost:8000)
php artisan queue:work                   # Process background jobs
composer dev                             # Start server + queue + logs in parallel

# Code Quality
./vendor/bin/pint                         # Format code with Pint
./vendor/bin/pint --test                  # Check formatting without fixing
```

## CODE CONVENTIONS

### 0. BASE RESPONSE METHODS

Always use the standardized response methods from the base `Controller` class for consistent API responses:

```php
// Success responses
$this->success($data, $message = 'Success', $statusCode = 200, $meta = [])
$this->created($data, $message = 'Success', $meta = [])
$this->noContent($message = null)  // Returns 204

// Error responses
$this->error($message, $statusCode = 400, $data = null, $meta = [])
$this->unauthorized($message = 'Unauthorized')
$this->forbidden($message = 'Forbidden')
$this->notFound($message = null, $resource = null)
$this->validationError($errors, $message = 'Validation error')

// Pagination
$this->paginate($paginator, $message = 'Success', $meta = [])
```

**Response Structure:**
```json
{
  "statusCode": 200,
  "message": "Success",
  "data": {},
  "meta": {}
}
```

### 1. VALIDATION - ALWAYS USE FormRequest Classes

Never validate inline. Create separate FormRequest classes:

```php
// Generate: php artisan make:request UserStoreRequest
class UserStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
        ];
    }
}

// Generate: php artisan make:request UserUpdateRequest
class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', Rule::unique(User::class)->ignore($this->route('user'))],
        ];
    }
}

// Controller usage
public function store(UserStoreRequest $request)
{
    User::create($request->validated());
}
```

### 2. CONTROLLERS - RESTful with Base Responses & Scramble Docs

```php
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;

#[Group('Users', weight: 0)]
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Get paginated list of users
     */
    #[Endpoint(title: 'List users', description: 'Get paginated list of users')]
    public function index()
    {
        $users = User::paginate(10);
        return $this->paginate($users, 'Success');
    }

    /**
     * Display the specified resource.
     *
     * Get a single user by ID
     */
    #[Endpoint(title: 'Get user', description: 'Get a single user by ID')]
    public function show(User $user)
    {
        return $this->success($user, 'Success');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Create a new user
     */
    #[Endpoint(title: 'Create user', description: 'Create a new user')]
    public function store(UserStoreRequest $request)
    {
        $user = User::create($request->validated());
        return $this->created($user, 'Success');
    }

    /**
     * Update the specified resource in storage.
     *
     * Update an existing user
     */
    #[Endpoint(title: 'Update user', description: 'Update an existing user')]
    public function update(UserUpdateRequest $request, User $user)
    {
        $user->update($request->validated());
        return $this->success($user, 'Success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Delete a user
     */
    #[Endpoint(title: 'Delete user', description: 'Delete a user')]
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            abort(403, 'Cannot delete your own account');
        }

        $user->delete();
        return $this->noContent();
    }
}
```

### 3. MODELS - Use PHP 8.2+ Casts Syntax

```php
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    // PHP 8.2+ cast syntax (method, not property)
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

### 4. MIGRATIONS - Anonymous Classes

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_name', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->unique();
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_name');
    }
};
```

### 5. ROUTING - API Resource with Sanctum

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('violations', ViolationController::class);
});
```

### 6. TESTING - Pest Framework

```php
// tests/Feature/UserTest.php
test('can list users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure(['data', 'meta', 'links']);
});

test('can create user', function () {
    $data = ['name' => 'John', 'email' => 'john@example.com'];

    $response = $this->postJson('/api/users', $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'John');
});
```

## NAMING CONVENTIONS

| Type | Pattern | Example |
|------|---------|---------|
| Migrations | `YYYY_MM_DD_HHMMSS_create_table_name_table.php` | `2026_02_13_123456_create_violations_table.php` |
| Controllers | `SingularController.php` | `UserController.php`, `ViolationController.php` |
| Models | `Singular.php` | `User.php`, `Violation.php` |
| FormRequests | `ActionModelRequest.php` | `UserStoreRequest.php`, `UserUpdateRequest.php` |
| API Routes | Plural | `/api/users`, `/api/violations` |

## ERROR HANDLING

```php
// Abort with custom message
abort(403, 'You do not have permission to perform this action');
abort(404, 'Resource not found');
abort(422, 'Validation failed');

// Validation errors handled automatically by Laravel
// Returns 422 with errors: { "message": "...", "errors": { "field": [...] } }
```

## DATABASE CONVENTIONS

- Use `$table->timestamps()` for created_at/updated_at
- Use `$table->nullable()` for optional columns
- Use `enum()` for fixed status/type values
- Foreign keys: `$table->foreignId()->constrained()->onDelete('cascade')`
- Always implement both `up()` and `down()` methods

## TESTING CONFIGURATION

- Database: SQLite in-memory (`DB_DATABASE=:memory:`)
- Framework: Pest (use `test()` function, not classes)
- Base test: `Tests\TestCase`
- Auth: `actingAs($user, 'sanctum')` for authenticated requests

## SANCTUM AUTHENTICATION

```php
// Generate token for user
$token = $user->createToken('api-token')->plainTextToken;

// Authenticated requests in tests
$this->actingAs($user, 'sanctum')->getJson('/api/users');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Protected routes here
});
```

## SCRAMBLE DOCUMENTATION

### Required Imports

```php
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Dedoc\Scramble\Attributes\ExcludeAllRoutesFromDocs;
```

### Group Attribute

Organize endpoints into logical groups with custom ordering and descriptions.

```php
#[Group(name: 'Users', description: 'User management endpoints', weight: 0)]
class UserController
{
    public function index() { /* Listed first in docs */ }
}

#[Group(name: 'Orders', description: 'Order processing', weight: 1)]
class OrderController
{
    public function index() { /* Listed after Users */ }
}
```

### Endpoint Attribute

Customize endpoint metadata such as operation ID, title, and description.

```php
#[Endpoint(
    operationId: 'processPayment',
    title: 'Process a payment',
    description: 'Processes a payment transaction and returns the result'
)]
public function process()
{
    // ...
}
```

### Exclude Routes from Documentation

Prevent specific endpoints or entire controllers from being included in the generated docs.

```php
// Exclude a single endpoint
#[ExcludeRouteFromDocs]
public function healthCheck()
{
    // Not documented
}

// Exclude all methods in a controller
#[ExcludeAllRoutesFromDocs]
class InternalController extends Controller
{
    // All methods excluded from docs
}
```

### Best Practices

1. **Use meaningful operationId values**: Use snake_case format with resource and action (e.g., `getUsers`, `createUser`)
2. **Keep descriptions concise**: Provide clear, actionable descriptions that explain what the endpoint does
3. **Group logically**: Organize related endpoints together with consistent `weight` values
4. **Document error responses**: Use PHPDoc to document response codes and error structures
5. **Exclude internal routes**: Use `ExcludeRouteFromDocs` for health checks, internal utilities
6. **Use descriptive titles**: Endpoint titles should be short but descriptive (3-5 words recommended)

### Documentation Access

After adding Scramble attributes, access your API documentation at:
- **Development**: `http://localhost:8000/docs/api`
- **Production**: `https://your-domain.com/docs/api` (ensure you've configured Scramble for production)

