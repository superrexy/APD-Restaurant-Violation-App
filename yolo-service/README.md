# OpenCV Camera CCTV with YOLO Detection & HLS Streaming

Modular CCTV stream viewer with YOLO object detection and HLS streaming capabilities.

## Features

- 📺 **Live CCTV Streaming** - View real-time CCTV feeds
- 🔍 **YOLO Object Detection** - Detect vehicles and people
- 🌐 **HLS Streaming** - HTTP-accessible stream for remote viewing
- ⚡ **SSE Streaming** - Low-latency streaming for browser viewing
- 🖥️ **Live Display** - Real-time preview with bounding boxes
- 🧹 **Auto Cleanup** - Automatic segment/file management
- 🏗️ **Modular Architecture** - Easy to maintain and extend

## Quick Start

### Prerequisites
- Python 3.x
- FFmpeg installed
- YOLO model file: `yolo26n.pt`

### Installation
```bash
# Install dependencies (recommended: use uv)
pip install opencv-python ultralytics numpy fastapi uvicorn[standard]

# Or using uv:
uv add fastapi "uvicorn[standard]"
```

### Usage

**Single command to start:**
```bash
python main.py
```

This starts:
1. CCTV stream reader
2. YOLO object detection
3. Live display window
4. HLS or SSE encoder (based on config)
5. HTTP server (port 8081)

### Streaming Modes

This application supports two streaming modes with FastAPI multi-client support:

### SSE Mode (Default - Low Latency)

**Configuration:**
```python
# config.py
OUTPUT_MODE = "sse"
```

**Usage:**
```bash
python main.py
```

**Access Stream:**
```
http://localhost:8081/stream
```

**HTML Client:**
```html
<!DOCTYPE html>
<html>
<body>
    <h1>Live CCTV Stream</h1>
    <img src="http://localhost:8081/stream" alt="Live Stream">
</body>
</html>
```

**Features:**
- ✅ Very low latency (~0.5s)
- ✅ Simple browser viewing
- ✅ No segment files needed
- ✅ Real-time object detection
- ✅ **Multi-client support (1-10 simultaneous viewers)**
- ❌ Only works in browsers
- ❌ No seeking support

---

## FastAPI Multi-Client Support

### Health Endpoint

Check system status and active client connections:

```bash
curl http://localhost:8081/health
```

**Response:**
```json
{
  "status": "ok",
  "mode": "sse",
  "active_clients": 2,
  "camera_status": true,
  "yolo_status": true,
  "streamer_status": true,
  "uptime_seconds": 123.45
}
```

**Status Fields:**
- `status`: Server health status ("ok" or error)
- `mode`: Current output mode ("sse" or "hls")
- `active_clients`: Number of currently connected SSE clients
- `camera_status`: True if camera stream is connected and producing frames
- `yolo_status`: True if YOLO detector is initialized and running
- `streamer_status`: True if FFmpegStreamer is running
- `uptime_seconds`: Server uptime in seconds

### Multi-Client Streaming

FastAPI-powered SSE streaming supports **1-10 simultaneous clients**:
- ✅ Real-time broadcast to all connected viewers
- ✅ Low latency (~0.5s)
- ✅ Automatic client disconnect handling
- ✅ Built-in logging with FastAPI logger
- ✅ Thread-safe status tracking

### Lifespan Configuration

Uses modern `lifespan` context manager (not deprecated `@app.on_event`):

```python
@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup: mount static files for HLS mode
    if config.OUTPUT_MODE == "hls":
        app.mount("/", StaticFiles(directory=config.OUTPUT_DIR))
    yield
    # Shutdown: cleanup
```

### Logging

FastAPI provides structured logging:
- **INFO**: Normal operations (client connect/disconnect, server startup)
- **ERROR**: Exceptions and failures

View logs in terminal where main.py is running.

### HLS Mode (Standard)

**Configuration:**
```python
# config.py
OUTPUT_MODE = "hls"
```

**Usage:**
```bash
python main.py
```

**Access Stream:**
```
http://localhost:8081/stream.m3u8
```

**Features:**
- ✅ Works with media players (VLC, etc.)
- ✅ Seeking support
- ✅ CDN-friendly
- ✅ Standard HLS format
- ❌ Higher latency (10-30s)

## Viewing

**Live Preview:**
- Automatic display window shows video with detection boxes
- Press 'q' to quit

**Stream URLs:**

- **SSE Mode:** `http://localhost:8081/stream` (img tag)
- **HLS Mode:** `http://localhost:8081/stream.m3u8` (HLS player)
- **Health Status:** `http://localhost:8081/health` (JSON API)

Open in:
- VLC Player (HLS mode only)
- Web browser (both modes)
- hls.js demo: https://video-dev.github.io/hls.js/demo/

## Project Structure

```
opencv-camera-cctv/
├── config.py                  # Centralized configuration
├── main.py                   # Main orchestrator
├── modules/                  # Modular components
│   ├── __init__.py          # Package exports
│   ├── http_server.py       # HTTP server (HLS + SSE)
│   ├── ffmpeg_ops.py        # FFmpeg operations
│   ├── yolo_detector.py    # YOLO detection
│   ├── hls_manager.py      # HLS playlist/segment management
│   └── sse_encoder.py     # SSE encoder (low latency)
├── simple.py                # Alternative simple viewer
├── yolo26n.pt             # YOLO model file
├── output/hls/             # Output directory (auto-cleaned)
├── README.md               # This file
└── HLS_README.md           # Detailed HLS documentation
```

## Modules

### `config.py`
Centralized configuration for all components.

### `modules/http_server.py`
FastAPI-based HTTP server with multi-client SSE support:
- Multi-client SSE streaming (1-10 simultaneous viewers)
- Health endpoint with system status monitoring
- Thread-safe status tracking for YOLO, camera, and streamer
- Modern lifespan context manager (startup/shutdown)
- Built-in CORS support and logging

### `modules/ffmpeg_ops.py`
FFmpeg operations including:
- `FFmpegStreamer` - Read stream to raw frames
- `FFmpegHLSEncoder` - Encode frames to HLS
- `StreamInfo` - Probe stream dimensions/FPS

### `modules/yolo_detector.py`
YOLO object detection wrapper.

### `modules/hls_manager.py`
HLS playlist validation and segment cleanup.

### `modules/sse_encoder.py`
SSE encoder for low-latency streaming:
- JPEG encoding
- Multipart/x-mixed-replace format
- Boundary markers

### `main.py`
Orchestrates all modules together.

## Configuration

Edit `config.py`:

```python
# Stream
STREAM_URL = "https://..."      # CCTV stream URL

# Output Mode
OUTPUT_MODE = "sse"              # "sse" (default, low latency) or "hls" (standard)

# HTTP Server
PORT = 8081                     # Server port (dynamic, from config.py)

# SSE Settings (SSE mode only)
SSE_BOUNDARY = "frame"           # Multipart boundary
SSE_JPEG_QUALITY = 85            # JPEG quality (1-100, higher = better)
SSE_MAX_QUEUE_SIZE = 10           # Max frames in queue

# HLS Settings (HLS mode only)
OUTPUT_DIR = "output/hls"       # Output directory
HLS_TIME = 10                   # Segment duration (seconds)
HLS_LIST_SIZE = 10              # Max segments in playlist
HLS_DELETE_THRESHOLD = 1         # Buffer segments

# YOLO
YOLO_MODEL_PATH = "yolo26n.pt"  # Model file
YOLO_CLASSES = [1,2,3,5,7]      # Classes to detect
YOLO_DEVICE = "mps"               # Device

# Display
DISPLAY_WINDOW_NAME = "Video Stream"
QUIT_KEY = 'q'
```

## YOLO Detection

Detects classes:
- Person
- Bicycle
- Car
- Bus
- Truck

Model: `yolo26n.pt` (nano version for speed)

## Output

**SSE Mode:**
- Frames encoded as JPEG
- Pushed via multipart/x-mixed-replace
- No intermediate files needed
- ~0.5s latency
- Queue size: 10 frames max

**HLS Mode:**
- Automatically created in `output/hls/`
- Rolling window: ~11 segments max
- Auto-deletion of old segments
- ~40MB max disk usage

**Auto Cleanup:**
- On startup: Clears all files in output/hls/
- Ensures fresh start each run

**Live display:**
- OpenCV window with real-time detection
- Bounding boxes for detected objects
- Class labels and confidence scores

## Development

### Adding New Modules

1. Create file in `modules/`
2. Add import to `modules/__init__.py`
3. Use in `main.py`

### Testing Individual Modules

```python
# Test YOLO detector
from modules import YOLODetector

detector = YOLODetector()
frame = cv2.imread("test.jpg")
result = detector.detect(frame)
```

```python
# Test stream info
from modules import StreamInfo

width, height = StreamInfo.get_dimensions("https://...")
fps = StreamInfo.get_fps("https://...")
```

## Troubleshooting

See `HLS_README.md` for detailed troubleshooting.

### Common Issues

**Stream not loading:**
- Check FFmpeg: `ffmpeg -version`
- Verify stream URL is accessible
- Check network connectivity
- Verify mode in config.py (sse or hls)

**No display window:**
- Install OpenCV: `pip install opencv-python`
- macOS: Install Python-tk framework

**SSE 404 errors:**
- Verify `main.py` is running
- Check that `OUTPUT_MODE = "sse"` in config.py
- Access: http://localhost:8081/stream
- Check health endpoint: http://localhost:8081/health

**HLS 404 errors:**
- Verify `main.py` is running
- Check that `OUTPUT_MODE = "hls"` in config.py
- Access: http://localhost:8081/stream.m3u8

### Common Issues

**Stream not loading:**
- Check FFmpeg: `ffmpeg -version`
- Verify stream URL is accessible
- Check network connectivity

**No display window:**
- Install OpenCV: `pip install opencv-python`
- macOS: Install Python-tk framework

**HLS 404 errors:**
- Verify `main.py` is running
- Check `output/hls/` directory
- Ensure server started successfully

**Module import errors:**
- Ensure running from project root
- Check `modules/` directory exists
- Verify `modules/__init__.py` is present

## Notes

- Press `q` in display window or `Ctrl+C` in terminal to stop
- FastAPI server auto-starts in background thread
- Multi-client SSE supports 1-10 simultaneous viewers
- Health endpoint provides real-time system status
- HLS encoder runs with low-latency settings (HLS mode)
- Segments cleaned up automatically (HLS mode)
- CORS enabled for browser access
- Modular architecture for easy maintenance

## Architecture Benefits

**Before Refactoring:**
- 1 file, 271 lines
- Mixed responsibilities
- Hard to test
- Difficult to maintain
- Single-client SSE only

**After Refactoring:**
- 5 modules + config
- main.py: ~100 lines (clean orchestrator)
- Single responsibility per module
- Easy to test independently
- Simple to extend/modify
- **FastAPI multi-client support (1-10 clients)**
- **Health endpoint with status monitoring**

## License

MIT
