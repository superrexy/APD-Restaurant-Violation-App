"""HTTP Server Module with FastAPI - Multi-Client SSE Support"""

import asyncio
import threading
import time
from contextlib import asynccontextmanager

import config
from fastapi import FastAPI, Response
from fastapi.responses import StreamingResponse
from fastapi.middleware.cors import CORSMiddleware
from fastapi.logger import logger


class SystemStatus:
    """Thread-safe system status tracker for health endpoint"""

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
                "source_type": config.STREAM_SOURCE_TYPE,
                "uptime_seconds": time.time() - self.start_time,
            }


system_status = SystemStatus()

current_frame: asyncio.Queue[bytes] = None
loop: asyncio.AbstractEventLoop = None


class FrameQueue:
    """Adapter for backward compatibility with existing encoder"""

    def __init__(self, maxsize: int = 10):
        self.ready = False

    def set_ready(self):
        self.ready = True
        logger.info("Frame queue ready")

    def put(self, frame: bytes, timeout: float = 0.1) -> bool:
        if not self.ready or not loop or not current_frame:
            return False
        try:
            try:
                current_frame.put_nowait(frame)
                return True
            except asyncio.QueueFull:
                logger.debug("Queue full, skipping frame")
                return False
        except Exception as e:
            logger.error(f"Queue error: {e}")
            return False

    def get(self) -> bytes:
        return None


frame_queue = FrameQueue(maxsize=config.SSE_MAX_QUEUE_SIZE)


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Lifespan context manager for startup/shutdown"""
    global current_frame, loop

    logger.info(f"FastAPI starting in {config.OUTPUT_MODE} mode")

    loop = asyncio.get_event_loop()
    current_frame = asyncio.Queue(maxsize=1)
    frame_queue.set_ready()

    if config.OUTPUT_MODE == "hls":
        from fastapi.staticfiles import StaticFiles

        app.mount("/", StaticFiles(directory=config.OUTPUT_DIR), name="static")
        logger.info(f"Static files mounted: {config.OUTPUT_DIR}")

    yield

    logger.info("FastAPI shutting down")


app = FastAPI(lifespan=lifespan)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["GET", "OPTIONS"],
    allow_headers=["*"],
)


@app.get("/stream")
async def stream_endpoint():
    """SSE streaming endpoint"""
    if config.OUTPUT_MODE != "sse":
        return {"error": "SSE mode not enabled"}

    system_status.update_client_count(1)
    logger.info(f"Client connected. Active: {system_status.active_clients}")

    async def generate_frames():
        try:
            while True:
                try:
                    frame = await asyncio.wait_for(current_frame.get(), timeout=1.0)
                    yield frame
                except asyncio.TimeoutError:
                    continue
        except asyncio.CancelledError:
            logger.info("Stream cancelled by client")
        except Exception as e:
            logger.error(f"Stream error: {e}")
        finally:
            system_status.update_client_count(-1)
            logger.info(f"Client disconnected. Active: {system_status.active_clients}")

    return StreamingResponse(generate_frames(), media_type=config.SSE_CONTENT_TYPE)


@app.get("/health")
async def health_endpoint():
    """Health check endpoint"""
    return system_status.get_status_dict()


def start_http_server(port: int, directory: str, output_mode: str = "hls"):
    """Start FastAPI server in blocking mode

    Args:
        port: Server port number
        directory: Directory to serve (for HLS mode)
        output_mode: "hls" or "sse"
    """
    logger.info(f"Starting FastAPI server: mode={output_mode}, port={port}")

    import uvicorn

    if output_mode == "sse":
        logger.info(f"SSE endpoint: http://localhost:{port}/stream")
        logger.info(f"Health endpoint: http://localhost:{port}/health")
    else:
        logger.info(f"HLS endpoint: http://localhost:{port}/stream.m3u8")

    uvicorn.run(app, host="0.0.0.0", port=port, log_level="info")
