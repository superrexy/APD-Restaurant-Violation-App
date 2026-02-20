"""Configuration file for HLS CCTV Streamer"""

# Stream Configuration
STREAM_SOURCE_TYPE = "file"  # url, webcam, or file
STREAM_URL = "https://cctv-dishub.tangerangkab.go.id/storage/video/01jsrepbfdrenxw1k42nxw94c5/01jsrepbfdrenxw1k42nxw94c5.m3u8"
WEBCAM_DEVICE_INDEX = 0
STREAM_FILE_PATH = "assets/demo.mp4"

# HTTP Server Configuration
PORT = 8081

# HLS Output Configuration
OUTPUT_DIR = "output/hls"
OUTPUT_FILE = f"{OUTPUT_DIR}/stream.m3u8"
HLS_TIME = 10
HLS_LIST_SIZE = 10
HLS_DELETE_THRESHOLD = 1

# YOLO Configuration
YOLO_MODEL_PATH = "models/best.pt"
YOLO_CLASSES = [0, 1, 2, 3, 4, 5]  # apron, hairnet, mask, no-apron, no-hairnet, no-mask
YOLO_DEVICE = "mps"

# Backend Integration Configuration
CAMERA_CODE = "CAM001"
BACKEND_API_URL = "http://localhost:8000"
BACKEND_API_KEY = "test-api-key"
VIOLATION_DELAY = 5  # seconds (range: 3-10)

# Display Configuration
DISPLAY_WINDOW_NAME = "Video Stream"
QUIT_KEY = "q"

# FFmpeg Configuration
FFMPEG_PROTOCOL_WHITELIST = "file,http,https,tcp,tls,crypto"
FFMPEG_LOGLEVEL = "error"
FFMPEG_TIMEOUT = 10

# Output Mode Configuration
OUTPUT_MODE = "sse"

# SSE Configuration
SSE_BOUNDARY = "frame"
SSE_JPEG_QUALITY = 85
SSE_CONTENT_TYPE = "multipart/x-mixed-replace; boundary=frame"
SSE_MAX_QUEUE_SIZE = 10

# Configuration Validation
assert 3 <= VIOLATION_DELAY <= 10, "VIOLATION_DELAY must be between 3 and 10 seconds"
assert isinstance(CAMERA_CODE, str) and CAMERA_CODE, (
    "CAMERA_CODE must be a non-empty string"
)
assert isinstance(BACKEND_API_URL, str) and BACKEND_API_URL, (
    "BACKEND_API_URL must be a non-empty string"
)
assert isinstance(BACKEND_API_KEY, str), "BACKEND_API_KEY must be a string"
assert STREAM_SOURCE_TYPE in ["url", "webcam", "file"], (
    "STREAM_SOURCE_TYPE must be 'url', 'webcam', or 'file'"
)
