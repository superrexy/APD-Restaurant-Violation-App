# AGENTS.md - Coding Agent Guidelines

Coding agent guidelines for the APD Restaurant Violation project. This document provides standards for development, architecture patterns, and conventions.

---

## Project Overview

APD Restaurant Violation is a restaurant violation detection and management system with:

- **Backend**: REST API for data management and violation tracking
- **Frontend**: Web dashboard for viewing reports, managing profiles, and configuring cameras
- **YOLO Service**: Real-time object detection for restaurant PPE compliance (apron, hairnet, mask)

### Tech Stack

| Layer                    | Technology                  | Version |
| ------------------------ | --------------------------- | ------- |
| **Backend**              | Laravel                     | 12.x    |
| **Backend PHP**          | PHP                         | 8.2+    |
| **Backend DB**           | PostgreSQL / SQLite         | -       |
| **Backend Auth**         | Laravel Sanctum             | 4.x     |
| **Backend WebSocket**    | Laravel Reverb              | 1.x     |
| **Backend Testing**      | Pest                        | 4.x     |
| **Backend API Docs**     | Scramble                    | 0.13+   |
| **Frontend**             | React                       | 19.x    |
| **Frontend Router**      | TanStack Router             | 1.132+  |
| **Frontend Build**       | Vite                        | 7.x     |
| **Frontend Language**    | TypeScript                  | 5.7+    |
| **Frontend Styling**     | Tailwind CSS                | 4.x     |
| **Frontend UI**          | Radix UI + Shadcn           | -       |
| **Frontend Lint/Format** | Biome                       | 2.x     |
| **Frontend HTTP Client** | Axios                       | 1.x     |
| **Frontend State**       | nuqs (URL state) + useState | -       |
| **Frontend Tables**      | TanStack Table              | 8.x     |
| **YOLO Service Python**  | Python                      | 3.12+   |
| **YOLO Service**         | Ultralytics YOLO            | 8.x     |
| **YOLO Service**         | FastAPI                     | 0.128+  |
| **YOLO Service**         | OpenCV                      | 4.13+   |
| **YOLO Service**         | Uvicorn                     | 0.40+   |
| **YOLO Package Manager** | uv                          | -       |

---

## Repository Structure

```
apd-restaurant-violation-app/
├── backend/                          # Laravel 12 REST API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/          # API Controllers
│   │   │   ├── Middleware/           # Custom middleware
│   │   │   └── Requests/             # FormRequest validation
│   │   ├── Models/                   # Eloquent models
│   │   ├── Exceptions/               # Exception handlers
│   │   └── Providers/                # Service providers
│   ├── bootstrap/
│   │   └── app.php                   # App configuration (Laravel 12 style)
│   ├── config/                       # Configuration files
│   ├── database/
│   │   ├── migrations/               # Database migrations
│   │   ├── seeders/                  # Database seeders
│   │   └── factories/                # Model factories
│   ├── routes/
│   │   ├── api.php                   # API routes
│   │   └── web.php                   # Web routes
│   ├── tests/
│   │   ├── Feature/                  # Feature tests (Pest)
│   │   └── Unit/                     # Unit tests (Pest)
│   ├── vendor/                       # Composer dependencies
│   ├── artisan                       # CLI command tool
│   ├── composer.json                 # PHP dependencies
│   └── AGENTS.md                     # Backend-specific guidelines
│
├── frontend/                         # React + TanStack Router
│   ├── src/
│   │   ├── api/
│   │   │   └── rest/                 # REST API client
│   │   ├── components/
│   │   │   ├── ui/                   # Shadcn UI components
│   │   │   ├── data-table/           # Table components
│   │   │   └── *.tsx                 # Shared components
│   │   ├── config/                   # App configuration
│   │   ├── features/                 # Feature-based pages
│   │   ├── types/                    # TypeScript types (moved)
│   │   ├── hooks/                    # Custom React hooks
│   │   ├── layouts/                  # Layout components
│   │   ├── lib/                      # Utility functions
│   │   ├── routes/                   # TanStack Router routes
│   │   ├── main.tsx                  # App entry
│   │   └── styles.css                # Global styles
│   ├── public/                       # Static assets

│   ├── package.json                  # Node dependencies
│   ├── vite.config.ts                # Vite configuration
│   ├── tsconfig.json                 # TypeScript config
│   └── biome.json                    # Biome lint/format config
│
├── yolo-service/                     # YOLO Detection Service
│   ├── modules/                      # Modular components
│   │   ├── __init__.py              # Package exports
│   │   ├── http_server.py           # FastAPI server (SSE + HLS)
│   │   ├── ffmpeg_ops.py            # FFmpeg stream operations
│   │   ├── yolo_detector.py         # YOLO detection wrapper
│   │   ├── hls_manager.py           # HLS playlist management
│   │   └── sse_encoder.py          # SSE JPEG encoder
│   ├── output/hls/                  # HLS output directory (auto-cleaned)
│   ├── config.py                     # Centralized configuration
│   ├── main.py                      # Main orchestration
│   ├── pyproject.toml                # Python dependencies (uv)
│   ├── README.md                     # YOLO service documentation
│   └── yolo26n.pt                  # YOLO model file
│
├── AGENTS.md                         # This file
└── README.md                         # Project documentation
```

---

## Environment Variables

### Backend (`backend/.env`)

```env
# Application
APP_NAME="APD Restaurant Violation"
APP_ENV=local
APP_KEY=                    # Generate: php artisan key:generate
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql         # pgsql, mysql, mariadb, or sqlite
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=apd_violation
DB_USERNAME=postgres
DB_PASSWORD=

# Cache & Session
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Broadcasting
BROADCAST_CONNECTION=reverb # or log

# API Key (for external services)
VIOLATION_API_KEY=your-api-key-here
```

### Frontend (`frontend/.env`)

```env
VITE_BASE_API_URL=http://localhost:8000
VITE_YOLO_SERVICE_URL=localhost:8081
```

### YOLO Service (`yolo-service/config.py`)

```python
# Stream Configuration
STREAM_URL = "https://cctv-url/stream.m3u8"

# HTTP Server Configuration
PORT = 8081

# Output Mode: "sse" (low latency) or "hls" (standard)
OUTPUT_MODE = "sse"

# YOLO Configuration
YOLO_MODEL_PATH = "yolo26n.pt"
YOLO_CLASSES = [0, 1, 2]  # Custom class IDs for apron, hairnet, mask
YOLO_DEVICE = "mps"  # mps, cuda, or cpu

# SSE Configuration (SSE mode only)
SSE_BOUNDARY = "frame"
SSE_JPEG_QUALITY = 85
SSE_MAX_QUEUE_SIZE = 10

# HLS Configuration (HLS mode only)
OUTPUT_DIR = "output/hls"
HLS_TIME = 10
HLS_LIST_SIZE = 10
HLS_DELETE_THRESHOLD = 1
```

---

## Development Commands

### Backend Commands

```bash
cd backend

# Setup
composer install             # Install dependencies
composer setup               # Full project setup
php artisan key:generate     # Generate app key

# Database
php artisan migrate          # Run migrations
php artisan migrate:fresh    # Fresh database (wipes data)
php artisan db:seed          # Run seeders

# Development Server
php artisan serve            # Start dev server (port 8000)
php artisan queue:work       # Process background jobs
composer dev                 # Parallel: server + queue + logs + vite

# Code Generation
php artisan make:controller UserController --api
php artisan make:model User -mcr
php artisan make:migration create_table_name_table
php artisan make:request UserStoreRequest

# Testing
php artisan test                          # Run all tests
php artisan test --filter testMethodName  # Run single test
./vendor/bin/pest                         # Pest runner

# Code Quality
./vendor/bin/pint            # Format code
./vendor/bin/pint --test     # Check formatting

# API Documentation
# Access at: http://localhost:8000/docs/api
```

### Frontend Commands

```bash
cd frontend

# Setup
bun install                  # Install dependencies

# Development
bun dev                      # Start dev server (port 3000)

# Build
bun run build                # Build for production
bun run serve                # Preview production build

# Testing
bun run test                 # Run Vitest

# Code Quality
bun run lint                 # Lint with Biome
bun run format               # Format with Biome
bun run check                # Lint + format check

# UI Components
pnpx shadcn@latest add button    # Add Shadcn component

# Storybook
bun run storybook            # Start Storybook (port 6006)
```

### YOLO Service Commands

```bash
cd yolo-service

# Setup (with uv)
uv sync                    # Install dependencies

# Run Service
python main.py              # Start YOLO detection service

# Dependencies
uv add fastapi "uvicorn[standard]" opencv-python ultralytics

# Model Management
# Download custom YOLO model for apron/hairnet/mask detection
# Place in: yolo-service/yolo26n.pt
```

---

### YOLO Service: FastAPI + YOLOv8 Conventions

#### Module Architecture

YOLO service uses a modular architecture with single responsibility per module:

| Module                 | File                       | Responsibility                                              |
| ---------------------- | -------------------------- | ----------------------------------------------------------- |
| **Main Orchestration** | `main.py`                  | Coordinate all modules, main streaming loop, error recovery |
| **Configuration**      | `config.py`                | Centralized single source of truth for all settings         |
| **HTTP Server**        | `modules/http_server.py`   | FastAPI server, SSE streaming, health monitoring            |
| **Stream I/O**         | `modules/ffmpeg_ops.py`    | FFmpeg stream reader, HLS encoder, stream probing           |
| **Object Detection**   | `modules/yolo_detector.py` | YOLO inference, frame annotation                            |
| **HLS Management**     | `modules/hls_manager.py`   | Playlist validation, segment cleanup                        |
| **SSE Encoding**       | `modules/sse_encoder.py`   | JPEG encoding for multipart streaming                       |

#### Data Flow Pattern

```
CCTV Stream → FFmpegStreamer → Frame (BGR) → YOLODetector → Annotated Frame
                                                            │
                                          ┌─────────────────┴─────────────────┐
                                          │ OUTPUT_MODE Selection               │
                                          └─────────────────┬─────────────────┘
                           ┌────────────────────────────────────────────────────┐
                           │                                            │
                  SSE Mode (Low Latency)                       HLS Mode (Seekable)
                           │                                            │
                           ▼                                            ▼
              SSEncoder.encode_frame()                     FFmpegHLSEncoder.write_frame()
                (JPEG + multipart)                              (H.264 segments)
                           │                                            │
                           ▼                                            ▼
                  FrameQueue.put()                              output/hls/*.ts
                           │                                            │
                           ▼                                            ▼
              FastAPI /stream endpoint                     FastAPI StaticFiles
         (StreamingResponse - multipart)                      (GET /stream.m3u8)
```

#### YOLO Detector Pattern

Always use YOLODetector wrapper for inference:

```python
from modules import YOLODetector
import config

# Initialize detector
detector = YOLODetector(
    model_path=config.YOLO_MODEL_PATH,
    device=config.YOLO_DEVICE,
    classes=config.YOLO_CLASSES,
)

# Detection loop
while True:
    frame = get_frame_from_stream()
    annotated_frame = detector.detect(frame)
    # Process or output annotated frame
```

**YOLODetector Implementation:**

```python
class YOLODetector:
    """YOLO object detector"""

    def __init__(self, model_path: str = None, device: str = None, classes: list = None):
        model_path = model_path or config.YOLO_MODEL_PATH
        device = device or config.YOLO_DEVICE
        classes = classes or config.YOLO_CLASSES

        self.model = YOLO(model_path)
        self.device = device
        self.classes = classes

    def detect(self, frame: np.ndarray) -> np.ndarray:
        """Run detection and return annotated frame"""
        results = self.model(frame, device=self.device, verbose=False, classes=self.classes)[0]
        annotated_frame = results.plot()
        return annotated_frame
```

**Detection Classes:**

- `0`: apron
- `1`: hairnet
- `2`: mask
- `3`: no-apron
- `4`: no-hairnet
- `5`: no-mask

#### FastAPI SSE Streaming Pattern

Use FastAPI StreamingResponse with multipart/x-mixed-replace:

```python
from fastapi import FastAPI, Response
from fastapi.responses import StreamingResponse
from contextlib import asynccontextmanager
import asyncio
import config

# Global queue for latest frame
current_frame: asyncio.Queue[bytes] = asyncio.Queue(maxsize=1)

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Initialize event loop and queue on startup"""
    global current_frame
    loop = asyncio.get_event_loop()
    current_frame = asyncio.Queue(maxsize=1)
    yield

app = FastAPI(lifespan=lifespan)

@app.get("/stream")
async def stream_endpoint():
    """SSE streaming endpoint for browser viewing"""
    if config.OUTPUT_MODE != "sse":
        return {"error": "SSE mode not enabled"}

    async def generate_frames():
        try:
            while True:
                frame = await asyncio.wait_for(current_frame.get(), timeout=1.0)
                yield frame  # Multipart JPEG data
        except asyncio.CancelledError:
            pass
        except Exception as e:
            logger.error(f"Stream error: {e}")

    return StreamingResponse(
        generate_frames(),
        media_type=config.SSE_CONTENT_TYPE
    )
```

#### State Management

Use thread-safe SystemStatus for health monitoring:

```python
import threading
import time

class SystemStatus:
    """Thread-safe system status tracker"""

    def __init__(self):
        self._lock = threading.Lock()
        self.yolo_status = False
        self.camera_status = False
        self.streamer_status = False
        self.start_time = time.time()
        self.active_clients = 0

    def set_yolo_status(self, status: bool):
        with self._lock:
            self.yolo_status = status

    def set_camera_status(self, status: bool):
        with self._lock:
            self.camera_status = status

    def set_streamer_status(self, status: bool):
        with self._lock:
            self.streamer_status = status

    def update_client_count(self, delta: int):
        with self._lock:
            self.active_clients += delta

    def get_status_dict(self) -> dict:
        with self._lock:
            return {
                "status": "ok",
                "mode": config.OUTPUT_MODE,
                "active_clients": self.active_clients,
                "camera_status": self.camera_status,
                "yolo_status": self.yolo_status,
                "streamer_status": self.streamer_status,
                "uptime_seconds": time.time() - self.start_time,
            }
```

**Health Endpoint:**

```python
@app.get("/health")
async def health_endpoint():
    """Health check endpoint"""
    return system_status.get_status_dict()
```

#### Error Handling

**Main Loop Pattern (Graceful Degradation):**

```python
while True:
    streamer = None
    try:
        streamer = FFmpegStreamer(config.STREAM_URL, width, height)
        streamer.start()
        system_status.set_camera_status(True)
        print("[Main] Camera stream connected")

        # Main processing loop
        while True:
            frame = streamer.get_frame()
            if frame is None:
                break
            # Process frame...

    except KeyboardInterrupt:
        print("\n[Main] Stopping...")
        break
    except Exception as e:
        system_status.set_camera_status(False)
        print(f"[Main] Error: {e}")
        print("[Main] Restarting in 3 seconds...")
        time.sleep(3)
    finally:
        if streamer:
            streamer.stop()
```

**FrameQueue Pattern (Skip Frames on Error):**

```python
class FrameQueue:
    """Adapter for backward compatibility"""

    def put(self, frame: bytes, timeout: float = 0.1) -> bool:
        if not current_frame:
            return False
        try:
            current_frame.put_nowait(frame)
            return True
        except asyncio.QueueFull:
            logger.debug("Queue full, skipping frame")
            return False
        except Exception as e:
            logger.error(f"Queue error: {e}")
            return False
```

#### Configuration Management

All settings in centralized `config.py`:

```python
# Stream Configuration
STREAM_URL = "https://cctv-url/stream.m3u8"

# HTTP Server Configuration
PORT = 8081

# Output Mode: "sse" (low latency) or "hls" (standard)
OUTPUT_MODE = "sse"

# YOLO Configuration
YOLO_MODEL_PATH = "yolo26n.pt"
YOLO_CLASSES = [0, 1, 2, 3, 4, 5]  # apron, hairnet, mask, no-apron, no-hairnet, no-mask
YOLO_DEVICE = "mps"  # mps, cuda, or cpu

# SSE Configuration (SSE mode only)
SSE_BOUNDARY = "frame"
SSE_JPEG_QUALITY = 85
SSE_CONTENT_TYPE = "multipart/x-mixed-replace; boundary=frame"
SSE_MAX_QUEUE_SIZE = 10

# HLS Configuration (HLS mode only)
OUTPUT_DIR = "output/hls"
OUTPUT_FILE = f"{OUTPUT_DIR}/stream.m3u8"
HLS_TIME = 10
HLS_LIST_SIZE = 10
HLS_DELETE_THRESHOLD = 1

# FFmpeg Configuration
FFMPEG_PROTOCOL_WHITELIST = "file,http,https,tcp,tls,crypto"
FFMPEG_LOGLEVEL = "error"
FFMPEG_TIMEOUT = 10
```

#### Thread Safety Guidelines

**Critical Rules:**

1. Each thread/process must have its own YOLO model instance
2. Use `threading.Lock` for shared state (SystemStatus)
3. Use `asyncio.Queue` for sync producer → async consumer
4. FFmpeg stderr logging in daemon thread
5. Server runs in background daemon thread

**Thread Architecture:**

```
Main Thread:
  ├── FFmpegStreamer (subprocess)
  ├── YOLODetector (blocking inference)
  └── FrameQueue.put() (sync)

Daemon Thread:
  └── FFmpeg stderr logging

HTTP Server Thread (daemon):
  └── Uvicorn (asyncio event loop)
      ├── asyncio.Queue (frame delivery)
      └── SystemStatus (Lock-protected)
```

#### Naming Conventions

| Type      | Convention       | Example                               |
| --------- | ---------------- | ------------------------------------- |
| Files     | kebab-case       | `http_server.py`, `yolo_detector.py`  |
| Classes   | PascalCase       | `YOLODetector`, `SystemStatus`        |
| Functions | snake_case       | `encode_frame()`, `get_status_dict()` |
| Constants | UPPER_SNAKE_CASE | `STREAM_URL`, `YOLO_DEVICE`           |
| Modules   | kebab-case       | `modules/` directory                  |

---

## Code Style Guidelines

### Backend: Laravel 12 Conventions

#### Controllers

Always extend base `Controller` and use standardized response methods:

```php
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;

#[Group('Users', weight: 0)]
class UserController extends Controller
{
    #[Endpoint(title: 'List users', description: 'Get paginated list')]
    public function index()
    {
        $users = User::paginate(10);
        return $this->paginate($users, 'Success');
    }

    #[Endpoint(title: 'Create user')]
    public function store(UserStoreRequest $request)
    {
        $user = User::create($request->validated());
        return $this->created($user, 'Success');
    }

    #[Endpoint(title: 'Get user')]
    public function show(User $user)
    {
        return $this->success($user, 'Success');
    }

    #[Endpoint(title: 'Update user')]
    public function update(UserUpdateRequest $request, User $user)
    {
        $user->update($request->validated());
        return $this->success($user, 'Success');
    }

    #[Endpoint(title: 'Delete user')]
    public function destroy(User $user)
    {
        $user->delete();
        return $this->noContent();
    }
}
```

**Response Methods** (from base Controller):

```php
// Success
$this->success($data, $message = 'Success', $statusCode = 200, $meta = [])
$this->created($data, $message = 'Success', $meta = [])
$this->noContent($message = null)  // 204

// Error
$this->error($message, $statusCode = 400, $data = null, $meta = [])
$this->unauthorized($message = 'Unauthorized')
$this->forbidden($message = 'Forbidden')
$this->notFound($message = null, $resource = null)
$this->validationError($errors, $message = 'Validation error')

// Pagination
$this->paginate($paginator, $message = 'Success', $meta = [])
```

**Response Structure**:

```json
{
  "statusCode": 200,
  "message": "Success",
  "data": {},
  "meta": {}
}
```

#### Form Requests (Always Use)

Never validate inline. Create separate FormRequest classes:

```php
// Store Request
class UserStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}

// Update Request (with unique ignore)
class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique(User::class)->ignore($this->route('user'))],
            'password' => ['sometimes', 'string', 'min:8'],
        ];
    }
}
```

#### Models (PHP 8.2+ Casts Syntax)

```php
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    // Use method, not property
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }
}
```

#### Migrations (Anonymous Classes)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camera_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->enum('status', ['pending', 'reviewed', 'resolved'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
```

#### Routes

```php
// routes/api.php

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('violations', ViolationController::class)->except(['update']);
    Route::put('/violations/{violation}/status', [ViolationController::class, 'updateStatus']);
});

// API Key protected routes
Route::middleware('custom_api_key')->group(function () {
    Route::post('/violations', [ViolationController::class, 'store']);
});
```

#### Testing (Pest Framework)

```php
test('can list users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure(['statusCode', 'message', 'data', 'meta']);
});

test('can create user', function () {
    $data = ['name' => 'John', 'email' => 'john@example.com', 'password' => 'password123'];

    $response = $this->postJson('/api/users', $data);

    $response->assertStatus(201);
    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});
```

---

### Frontend: TanStack Router Conventions

#### File-Based Routing

Routes are defined in `src/routes/` using `createFileRoute()`:

```tsx
// src/routes/profiles/index.tsx
import { createFileRoute } from "@tanstack/react-router";
import ProfilePage from "@/features/ProfilePage";

export const Route = createFileRoute("/profiles/")({
  component: ProfilePage,
});
```

**Route with Params**:

```tsx
// src/routes/profiles/$profileId/edit.tsx
import { createFileRoute } from "@tanstack/react-router";
import ProfileFormPage from "@/features/ProfilePage/ProfileFormPage";

export const Route = createFileRoute("/profiles/$profileId/edit")({
  component: () => {
    const { profileId } = Route.useParams();
    return <ProfileFormPage profileId={profileId} mode="edit" />;
  },
});
```

**Root Layout** (`src/routes/__root.tsx`):

```tsx
import { Outlet, createRootRoute } from "@tanstack/react-router";
import { ThemeProvider } from "@/components/theme-provider";
import MainLayout from "@/layouts/MainLayout";

export const Route = createRootRoute({
  component: () => (
    <ThemeProvider defaultTheme="light">
      <MainLayout>
        <Outlet />
      </MainLayout>
    </ThemeProvider>
  ),
});
```

#### Navigation

```tsx
import { Link, useNavigate } from "@tanstack/react-router"

// Link component
<Link to="/profiles">Profiles</Link>
<Link to="/profiles/$profileId/edit" params={{ profileId: "123" }}>Edit</Link>

// Programmatic navigation
const navigate = useNavigate()
navigate({ to: '/profiles' })
navigate({ to: '/profiles/$profileId/edit', params: { profileId: '123' } })
```

#### REST API Pattern

```tsx
// Setup: src/api/rest/client.ts
import axios from "axios"

const api = axios.create({
  baseURL: import.meta.env.VITE_BASE_API_URL,
})

// API services exported from client.ts
export const authApi = { login, logout, refreshToken, getCurrentUser }
export const usersApi = { list, get, create, update, delete }
export const violationsApi = { list, get, create, updateStatus, delete }
export const violationTypesApi = { list, get, create, update, delete }
export const violationDetailsApi = { list, get, updateStatus, delete }

// Usage in component
import { usersApi } from "@/api/rest/client"
import { useRestApi } from "@/hooks/use-rest-api"

const UsersPage = () => {
  const { callAPI } = useRestApi()
  const [users, setUsers] = useState([])

  useEffect(() => {
    const fetchUsers = async () => {
      const response = await callAPI(() => usersApi.list({ page: 1, perPage: 10 }))
      if (response) setUsers(response.data)
    }
    fetchUsers()
  }, [])

  return <div>{/* render users */}</div>
}
```

#### Data Table Pattern

```tsx
import { useDataTable } from "@/hooks/use-data-table";
import { DataTable } from "@/components/data-table/data-table";

const ProfilePage = () => {
  const columns = useMemo<ColumnDef<Profile>[]>(
    () => [
      {
        id: "name",
        accessorKey: "name",
        header: ({ column }) => (
          <DataTableColumnHeader column={column} label="Name" />
        ),
        cell: ({ cell }) => <div>{cell.getValue<string>()}</div>,
        meta: { label: "Name", variant: "text" },
        enableColumnFilter: true,
      },
    ],
    [],
  );

  const { table } = useDataTable({
    data: profiles,
    columns,
    pageCount: totalPages,
    initialState: { sorting: [{ id: "createdAt", desc: true }] },
    getRowId: (row) => row.id,
  });

  return <DataTable table={table} columns={columns} />;
};
```

#### Form Pattern (React Hook Form + Zod)

```tsx
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";

const formSchema = z.object({
  name: z.string().min(1, "Name is required"),
  email: z.string().email("Invalid email"),
});

const ProfileForm = () => {
  const form = useForm<z.infer<typeof formSchema>>({
    resolver: zodResolver(formSchema),
    defaultValues: { name: "", email: "" },
  });

  const onSubmit = (values: z.infer<typeof formSchema>) => {
    // Submit logic
  };

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)}>
        <FormField name="name" render={({ field }) => <Input {...field} />} />
        <Button type="submit">Submit</Button>
      </form>
    </Form>
  );
};
```

#### Component Organization

| Directory                | Purpose                                       |
| ------------------------ | --------------------------------------------- |
| `features/`              | Page-level components organized by feature    |
| `components/ui/`         | Shadcn UI primitives                          |
| `components/data-table/` | Table-related components                      |
| `components/`            | Shared application components                 |
| `layouts/`               | Layout wrappers                               |
| `hooks/`                 | Custom React hooks                            |
| `lib/`                   | Utility functions (`cn`, formatters, parsers) |
| `config/`                | App configuration (sidebar, data-table)       |

#### Naming Conventions

| Type        | Convention                      | Example                              |
| ----------- | ------------------------------- | ------------------------------------ |
| Files       | kebab-case                      | `app-sidebar.tsx`, `use-rest-api.ts` |
| Components  | PascalCase                      | `AppSidebar`, `DataTable`            |
| Hooks       | camelCase with `use` prefix     | `useRestApi`, `useDataTable`         |
| Variables   | camelCase                       | `profileId`, `handleDelete`          |
| Types       | PascalCase                      | `Profile`, `PredictResult`           |
| Route files | kebab-case, `$param` for params | `profiles/$profileId/edit.tsx`       |

---

## Key Architectural Decisions

### YOLO Service

1. **Modular Architecture**: Single responsibility per module for maintainability
2. **Two Output Modes**: SSE (low latency ~0.5s) and HLS (seekable/recording)
3. **FastAPI with Lifespan**: Modern async context manager (not deprecated `@app.on_event`)
4. **Single-Element Queue**: `asyncio.Queue[1]` for latest frame only, minimal memory
5. **YOLO26n Model**: Nano variant for real-time performance on restaurant CCTV
6. **MPS Device**: Metal Performance Shaders for Apple Silicon optimization
7. **FFmpeg Subprocess Pattern**: Decouple frame processing from network I/O
8. **Thread-Safe State**: `SystemStatus` with `threading.Lock` for health monitoring
9. **Auto-Recovery**: Stream error → 3s retry loop for graceful degradation
10. **Health Endpoint**: Observability with camera, YOLO, and streamer status

---

## Key Architectural Decisions

### Backend

1. **REST API**: Standard RESTful endpoints with Laravel 12
2. **Sanctum Auth**: Token-based authentication for API consumers
3. **FormRequest**: All validation in dedicated request classes
4. **Base Response**: Standardized JSON responses via base Controller
5. **Scramble**: Auto-generated OpenAPI documentation
6. **Pest**: Modern testing framework with functional syntax
7. **Route Model Binding**: Implicit binding in controllers

### Frontend

1. **File-Based Routing**: TanStack Router with automatic route generation
2. **REST API**: Type-safe API communication via Axios with standardized responses
3. **nuqs**: URL state management for shareable table states
4. **Feature-Based**: Components organized by feature, not type
5. **Shadcn UI**: Accessible, customizable component library
6. **Biome**: Single tool for linting and formatting
7. **TypeScript Strict**: Full type safety throughout

---

## Related Documentation

- [Backend Guidelines](./backend/AGENTS.md) - Detailed Laravel 12 patterns
- [Frontend README](./frontend/README.md) - TanStack Router setup guide
- [YOLO Service README](./yolo-service/README.md) - YOLO detection service documentation
- [Laravel 12 Docs](https://laravel.com/docs/12.x) - Official documentation
- [TanStack Router](https://tanstack.com/router) - Official documentation
- [Ultralytics YOLO](https://docs.ultralytics.com) - YOLOv8 documentation
