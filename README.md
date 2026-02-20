# APD Restaurant Violation

Aplikasi deteksi pelanggaran restoran menggunakan YOLO object detection dengan CCTV streaming.

## 📋 Overview

Proyek ini terdiri dari tiga modul utama:

- **Backend** - Laravel API (PHP 8.2+)
- **Frontend** - TanStack React App (React 19)
- **Yolo Service** - Python FastAPI untuk deteksi object dan streaming CCTV

## 🏗️ Struktur Proyek

```
apd-restaurant-violation-app/
├── backend/           # Laravel API
├── frontend/          # React Frontend (TanStack)
├── yolo-service/      # Python YOLO Detection Service
└── README.md         # File ini
```

## 📋 Prasyarat

Pastikan komputer Anda sudah terinstall:

### Umum
- **Git** - untuk version control
- **Node.js** (v20+) dan **npm** atau **Bun** - untuk frontend dan backend frontend dependencies
- **PostgreSQL** - untuk database backend

### Backend (Laravel)
- **PHP 8.2** atau lebih tinggi
- **Composer** - PHP package manager
  - [Download Composer](https://getcomposer.org/download/)
  - Pastikan PHP extension: `pgsql`, `mbstring`, `xml`, `ctype`, `json`, `bcmath`

### Frontend
- **Node.js** (v20+) atau **Bun**
  - [Download Node.js](https://nodejs.org/)
  - [Download Bun](https://bun.sh/)

### Yolo Service
- **Python 3.12** atau lebih tinggi
  - [Download Python](https://www.python.org/downloads/)
  - Saat install, centang "Add Python to PATH"
- **FFmpeg** - untuk video processing
  - Download dari [ffmpeg.org](https://ffmpeg.org/download.html)
  - Extract dan tambahkan ke PATH environment variable
- **uv** (opsional tapi direkomendasikan) - Python package manager yang cepat
  - Install dengan: `pip install uv` atau download dari [astral.sh/uv](https://astral.sh/uv)

---

## 🚀 Panduan Instalasi (Windows)

### 1. Clone Repository

Buka Command Prompt / PowerShell dan jalankan:

```bash
git clone https://github.com/username/apd-restaurant-violation-app.git
cd apd-restaurant-violation-app
```

### 2. Setup Database (PostgreSQL)

#### Install PostgreSQL
1. Download [PostgreSQL Installer](https://www.postgresql.org/download/windows/)
2. Install dengan default settings
3. Ingat password yang Anda set saat install (default: `postgres`)
4. Setelah install, buka **pgAdmin** atau gunakan **psql** di terminal

#### Buat Database

Buka psql di terminal:

```bash
psql -U postgres
```

Masukkan password saat diminta, lalu jalankan:

```sql
CREATE DATABASE apd_restaurant_violation_be_v2;
\q
```

**Alternatif menggunakan pgAdmin:**
1. Buka pgAdmin
2. Right-click pada **Databases** > **Create** > **Database**
3. Nama database: `apd_restaurant_violation_be_v2`
4. Klik **Save**

---

### 3. Setup Backend

#### 3.1 Masuk ke directory backend

```bash
cd backend
```

#### 3.2 Install PHP Dependencies

```bash
composer install
```

#### 3.3 Setup Environment File

```bash
copy .env.example .env
```

#### 3.4 Generate Application Key

```bash
php artisan key:generate
```

#### 3.5 Konfigurasi Environment (.env)

Buka file `.env` dengan text editor (Notepad, VS Code, dll) dan konfigurasi:

```env
# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=apd_restaurant_violation_be_v2
DB_USERNAME=postgres
DB_PASSWORD=password_anda  # Ganti dengan password PostgreSQL Anda

# Application Configuration
APP_URL=http://localhost:8000
APP_DEBUG=true

# API Configuration
VIOLATION_API_KEY=test-api-key
```

**Catatan:** Ganti `password_anda` dengan password PostgreSQL yang Anda set saat install.

#### 3.6 Jalankan Migrasi Database

```bash
php artisan migrate
```

#### 3.7 Jalankan Database Seeders (Jika ada)

```bash
php artisan db:seed
```

Untuk menjalankan seeder tertentu:

```bash
php artisan db:seed --class=NamaSeeder
```

#### 3.8 Install Frontend Dependencies untuk Backend

```bash
npm install
# atau gunakan bun:
bun install
```

#### 3.9 Build Frontend Assets

```bash
npm run build
# atau:
bun run build
```

#### 3.10 Jalankan Backend

Untuk development, gunakan script dev:

```bash
composer run dev
```

Atau jalankan server secara manual:

```bash
php artisan serve
```

Backend akan berjalan di: **http://localhost:8000**

---

### 4. Setup Frontend

#### 4.1 Masuk ke directory frontend (dari root directory)

Buka terminal baru (tetap di directory root):

```bash
cd frontend
```

#### 4.2 Install Dependencies

Menggunakan npm:

```bash
npm install
```

Atau menggunakan Bun (lebih cepat):

```bash
bun install
```

#### 4.3 Setup Environment File

```bash
copy .env.example .env
```

#### 4.4 Konfigurasi Environment (.env)

Buka file `.env` dan pastikan:

```env
VITE_BASE_API_URL=http://localhost:8000
VITE_YOLO_SERVICE_URL=localhost:8081
```

#### 4.5 Jalankan Frontend Development Server

Menggunakan npm:

```bash
npm run dev
```

Atau menggunakan Bun:

```bash
bun run dev
```

Frontend akan berjalan di: **http://localhost:3000**

---

### 5. Setup Yolo Service

#### 5.1 Masuk ke directory yolo-service (dari root directory)

Buka terminal baru (tetap di directory root):

```bash
cd yolo-service
```

#### 5.2 Buat Virtual Environment (Python)

```bash
python -m venv .venv
```

#### 5.3 Aktifkan Virtual Environment

**Di Windows (Command Prompt):**

```bash
.venv\Scripts\activate
```

**Di Windows (PowerShell):**

```bash
.venv\Scripts\Activate.ps1
```

**Catatan:** Jika muncul error tentang execution policy di PowerShell, jalankan:

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

#### 5.4 Install Python Dependencies

Menggunakan pip:

```bash
pip install -r requirements.txt
```

Atau menggunakan uv (lebih cepat, jika sudah terinstall):

```bash
uv sync
```

Jika tidak ada `requirements.txt`, install secara manual:

```bash
pip install fastapi uvicorn[standard] opencv-python ultralytics
```

#### 5.5 Pastikan FFmpeg Terinstall

Cek di terminal:

```bash
ffmpeg -version
```

Jika tidak ditemukan:
1. Download FFmpeg dari [ffmpeg.org](https://ffmpeg.org/download.html)
2. Extract ke folder (misal: `C:\ffmpeg`)
3. Tambahkan ke PATH:
   - Buka **System Properties** > **Environment Variables**
   - Di **System Variables**, cari `Path` dan klik **Edit**
   - Klik **New** dan tambahkan: `C:\ffmpeg\bin`
   - Klik **OK** dan restart terminal

#### 5.6 Siapkan YOLO Model

Pastikan file model YOLO ada di directory `models/`:

```bash
# Cek apakah file model ada
dir models
```

Jika tidak ada, download model YOLO:

```bash
# Download dari Ultralytics (opsional)
python -c "from ultralytics import YOLO; YOLO('yolov8n.pt')"
```

Atau tempatkan file model Anda di: `models/best.pt` atau `models/yolov8n.pt`

#### 5.7 Konfigurasi (config.py)

Buka `config.py` dan sesuaikan:

```python
# Stream Configuration
STREAM_SOURCE_TYPE = "url"  # url, webcam, or file
STREAM_URL = "https://url-cctv-anda.com/stream.m3u8"
WEBCAM_DEVICE_INDEX = 0
STREAM_FILE_PATH = "assets/demo.mp4"

# Backend Integration
CAMERA_CODE = "CAM001"
BACKEND_API_URL = "http://localhost:8000"
BACKEND_API_KEY = "test-api-key"
VIOLATION_DELAY = 5  # seconds (range: 3-10)

# YOLO Configuration
YOLO_MODEL_PATH = "models/best.pt"
YOLO_CLASSES = [0, 1, 2, 3, 4, 5]  # sesuaikan dengan classes Anda
YOLO_DEVICE = "cpu"  # Gunakan "cuda" jika punya NVIDIA GPU

# Output Mode
OUTPUT_MODE = "sse"  # "sse" (low latency) atau "hls" (standard)
```

#### 5.8 Jalankan Yolo Service

```bash
python main.py
```

Service akan berjalan di: **http://localhost:8081**

**Endpoint yang tersedia:**
- Stream (SSE): `http://localhost:8081/stream`
- Stream (HLS): `http://localhost:8081/stream.m3u8` (jika `OUTPUT_MODE = "hls"`)
- Health Check: `http://localhost:8081/health`

---

## 🚀 Menjalankan Semua Services

Untuk menjalankan semua services secara bersamaan:

### Opsi 1: Tiga Terminal Terpisah

Buka 3 terminal terpisah:

**Terminal 1 - Backend:**
```bash
cd backend
composer run dev
# atau: php artisan serve
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
# atau: bun run dev
```

**Terminal 3 - Yolo Service:**
```bash
cd yolo-service
.venv\Scripts\activate
python main.py
```

### Opsi 2: Menggunakan Concurrently (Backend)

Backend sudah memiliki script `composer run dev` yang menjalankan:
- PHP artisan serve (port 8000)
- Queue listener
- Logs
- Vite dev server (untuk frontend assets)

Jalankan dari directory backend:

```bash
cd backend
composer run dev
```

Lalu jalankan yolo-service di terminal terpisah.

---

## 🔧 Troubleshooting

### Backend

**Error: "Could not open input file: artisan"**
- Pastikan Anda berada di directory `backend/`
- Cek file `artisan` ada di directory tersebut

**Error: "SQLSTATE[08006] [7] could not connect to server"**
- Pastikan PostgreSQL service berjalan
- Buka **Services** (Windows) dan cari "postgresql-x64-xx"
- Cek connection di `.env` (host, port, username, password)

**Error: "Class 'xxx' not found"**
- Jalankan: `composer dump-autoload`
- Cek `composer.json` dan jalankan: `composer install`

### Frontend

**Error: "Cannot find module" atau "Module not found"**
- Jalankan: `npm install` atau `bun install`
- Hapus `node_modules` dan install ulang:
  ```bash
  rmdir /s /q node_modules
  npm install
  ```

**Error: "VITE_BASE_API_URL is not defined"**
- Pastikan file `.env` ada di directory frontend
- Restart dev server

**Port 3000 sudah digunakan**
- Ubah port di package.json atau gunakan:
  ```bash
  npm run dev -- --port 3001
  ```

### Yolo Service

**Error: "ffmpeg not found"**
- Pastikan FFmpeg sudah terinstall
- Cek PATH environment variable
- Restart terminal setelah menambahkan FFmpeg ke PATH

**Error: "ImportError: No module named 'xxx'"**
- Pastikan virtual environment aktif
- Jalankan: `pip install -r requirements.txt` atau `uv sync`
- Pastikan semua dependencies terinstall

**Error: "FileNotFoundError: [Errno 2] No such file or directory: 'models/best.pt'"**
- Pastikan file model YOLO ada di directory `models/`
- Download model YOLO jika belum ada

**Error: "Connection refused" saat koneksi ke backend**
- Pastikan backend berjalan
- Cek `BACKEND_API_URL` di `config.py`
- Cek firewall Windows

---

## 📚 Dokumentasi Tambahan

- [Laravel Documentation](https://laravel.com/docs)
- [React Documentation](https://react.dev/)
- [TanStack Router](https://tanstack.com/router)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [FastAPI Documentation](https://fastapi.tiangolo.com/)
- [Ultralytics YOLO](https://docs.ultralytics.com/)

---

## 🧪 Testing

### Backend Tests

```bash
cd backend
composer run test
# atau:
php artisan test
```

### Frontend Tests

```bash
cd frontend
npm run test
# atau:
bun run test
```

---

## 🏗️ Build untuk Production

### Backend

```bash
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend

```bash
cd frontend
npm run build
# atau:
bun run build
```

Output build akan ada di directory `dist/`

### Yolo Service

Untuk production, gunakan production server:

```bash
uvicorn main:app --host 0.0.0.0 --port 8081 --workers 4
```

Atau gunakan **gunicorn**:

```bash
pip install gunicorn
gunicorn -w 4 -k uvicorn.workers.UvicornWorker main:app --bind 0.0.0.0:8081
```

---

## 📝 Catatan Penting

- Pastikan semua environment variables sudah dikonfigurasi dengan benar
- Jangan commit file `.env` ke version control
- Gunakan password yang kuat untuk database di production
- Jalankan services dalam urutan: Database → Backend → Frontend → Yolo Service
- Pastikan FFmpeg terinstall dan terkonfigurasi di PATH untuk Yolo Service
- Model YOLO harus tersedia di directory `models/` sebelum menjalankan service