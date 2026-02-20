"""HLS CCTV Streamer - Main Orchestration"""

import asyncio
import cv2
import threading
import time
import glob
import os
import numpy as np
from typing import Dict, List
from modules import (
    start_http_server,
    FFmpegStreamer,
    FFmpegHLSEncoder,
    YOLODetector,
    HLSManager,
    WebcamStreamer,
)
from modules import StreamInfo, SSEncoder, frame_queue, system_status
from modules import BackendClient, ViolationQueue
import config


def cleanup_output_dir():
    """Clear all files in output/hls directory

    This ensures a fresh start each time main.py is run.
    """
    try:
        if os.path.exists(config.OUTPUT_DIR):
            files = glob.glob(f"{config.OUTPUT_DIR}/*")
            for file in files:
                os.remove(file)
            print(f"[Main] Cleaned {len(files)} files from {config.OUTPUT_DIR}")
    except Exception as e:
        print(f"[Main] Cleanup error: {e}")


def map_violation_class_name_to_code(class_name: str) -> str:
    """Map YOLO class name to backend violation code

    Args:
        class_name: YOLO detected class name (e.g., "no-apron", "no-hairnet", "no-mask")

    Returns:
        Backend violation code (e.g., "NO_APRON", "NO_HAIRNET", "NO_MASK")
    """
    mapping = {
        "no-apron": "NO_APRON",
        "no-hairnet": "NO_HAIRNET",
        "no-mask": "NO_MASK",
    }
    return mapping.get(class_name, class_name.upper().replace("-", "_"))


class ViolationSubmitter:
    """Async violation submitter with throttling"""

    def __init__(self, backend_client: BackendClient, violation_queue: ViolationQueue):
        self.backend_client = backend_client
        self.violation_queue = violation_queue
        self.pending_violations: Dict[str, List[tuple]] = {}
        self._lock = threading.Lock()
        self.event_loop = None
        self.thread = None
        self.running = False

    def start(self):
        """Start the async submitter thread"""
        self.running = True
        self.thread = threading.Thread(target=self._run_loop, daemon=True)
        self.thread.start()
        print("[Main] Violation submitter started")

    def stop(self):
        """Stop the submitter thread"""
        self.running = False
        if self.thread and self.thread.is_alive():
            self.thread.join(timeout=5)
        print("[Main] Violation submitter stopped")

    def _run_loop(self):
        """Run the async event loop in separate thread"""
        self.event_loop = asyncio.new_event_loop()
        asyncio.set_event_loop(self.event_loop)

        try:
            while self.running:
                try:
                    self.event_loop.run_until_complete(self._process_violations())
                    time.sleep(0.1)
                except Exception as e:
                    print(f"[Main] Violation submitter error: {e}")
        finally:
            self.event_loop.close()

    async def _process_violations(self):
        """Process pending violations"""
        with self._lock:
            violations_to_submit = list(self.pending_violations.items())
            self.pending_violations.clear()

        for violation_type, frames in violations_to_submit:
            if self.violation_queue.can_submit(violation_type):
                for frame_data in frames[:1]:
                    await self._submit_single_violation(violation_type, frame_data)

    async def _submit_single_violation(self, violation_type: str, frame_data: tuple):
        """Submit a single violation to backend"""
        frame, detection_info = frame_data

        temp_path = f"/tmp/violation_{int(time.time())}_{violation_type}.jpg"
        cv2.imwrite(temp_path, frame)

        try:
            await self.backend_client.submit_violation(
                image_path=temp_path,
                violation_details=[
                    {
                        "violation_code": detection_info["violation_code"],
                        "confidence_score": None,
                        "additional_info": None,
                    }
                ],
                notes=detection_info.get("notes"),
            )
            print(f"[Main] Violation submitted: {violation_type}")
        except Exception as e:
            print(f"[Main] Failed to submit violation: {e}")
        finally:
            if os.path.exists(temp_path):
                os.remove(temp_path)

    def add_violation(
        self, frame: np.ndarray, violation_type: str, violation_code: str
    ):
        """Add a violation to the pending queue"""
        with self._lock:
            if violation_type not in self.pending_violations:
                self.pending_violations[violation_type] = []

            self.pending_violations[violation_type].append(
                (
                    frame,
                    {
                        "violation_code": violation_code,
                        "notes": f"Detected {violation_type}",
                    },
                )
            )

            # Keep only latest violation per type
            if len(self.pending_violations[violation_type]) > 1:
                self.pending_violations[violation_type] = self.pending_violations[
                    violation_type
                ][-1:]


def get_webcam_resolution(device_index: int) -> tuple[int, int]:
    """Get webcam resolution using OpenCV.

    Args:
        device_index: Webcam device index (0, 1, 2...)

    Returns:
        (width, height) tuple or default (640, 480) if unavailable
    """
    try:
        cap = cv2.VideoCapture(device_index)
        if cap.isOpened():
            width = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
            height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
            cap.release()
            if width > 0 and height > 0:
                return width, height
    except Exception as e:
        print(f"[Main] Could not get webcam resolution: {e}")
    return 640, 480


def get_stream_source() -> tuple[str | int, int, int]:
    """Determine stream source URL based on configuration type.

    Returns:
        (url_or_device_index, width, height) tuple for the selected source
        url_or_device_index is a string for URL/file, int for webcam
    """
    if config.STREAM_SOURCE_TYPE == "webcam":
        print(f"[Main] Using webcam device: {config.WEBCAM_DEVICE_INDEX}")
        width, height = get_webcam_resolution(config.WEBCAM_DEVICE_INDEX)
        print(f"[Main] Webcam resolution: {width}x{height}")
        return config.WEBCAM_DEVICE_INDEX, width, height

    elif config.STREAM_SOURCE_TYPE == "file":
        print(f"[Main] Using video file: {config.STREAM_FILE_PATH}")
        width, height = StreamInfo.get_dimensions(config.STREAM_FILE_PATH)
        return config.STREAM_FILE_PATH, width, height

    else:  # url
        print(f"[Main] Using stream URL: {config.STREAM_URL}")
        width, height = StreamInfo.get_dimensions(config.STREAM_URL)
        return config.STREAM_URL, width, height


def main():
    """Main orchestration function"""

    cleanup_output_dir()

    stream_source, width, height = get_stream_source()
    print(f"[Main] Output Mode: {config.OUTPUT_MODE}")

    # Get FPS - use default 30 for webcam, probe for URL/file
    if config.STREAM_SOURCE_TYPE == "webcam":
        fps = 30.0  # Default webcam FPS
    else:
        fps = StreamInfo.get_fps(str(stream_source))

    print(f"[Main] Resolution: {width}x{height}, FPS: {fps}")

    detector = YOLODetector(
        model_path=config.YOLO_MODEL_PATH,
        device=config.YOLO_DEVICE,
        classes=config.YOLO_CLASSES,
    )
    system_status.set_yolo_status(True)
    print("[Main] YOLO detector initialized")

    if config.OUTPUT_MODE == "sse":
        print("[Main] Using SSE streaming mode (low latency)")

        sse_encoder = SSEncoder(
            boundary=config.SSE_BOUNDARY, jpeg_quality=config.SSE_JPEG_QUALITY
        )

        encoder = None
        hls_manager = None

    else:
        print("[Main] Using HLS streaming mode")

        encoder = FFmpegHLSEncoder(
            width,
            height,
            fps,
            config.OUTPUT_FILE,
            config.HLS_TIME,
            config.HLS_LIST_SIZE,
            config.HLS_DELETE_THRESHOLD,
        )

        hls_manager = HLSManager(
            output_dir=config.OUTPUT_DIR,
            keep_count=config.HLS_LIST_SIZE + config.HLS_DELETE_THRESHOLD,
        )

        sse_encoder = None

    server_thread = threading.Thread(
        target=start_http_server,
        args=(config.PORT, config.OUTPUT_DIR, config.OUTPUT_MODE),
        daemon=True,
    )
    server_thread.start()
    time.sleep(0.5)

    violation_queue = ViolationQueue(delay_seconds=config.VIOLATION_DELAY)
    backend_client = BackendClient()
    violation_submitter = ViolationSubmitter(backend_client, violation_queue)
    violation_submitter.start()

    last_heartbeat_time = time.time()
    heartbeat_interval = 30

    # 3. Main streaming loop
    while True:
        streamer = None
        hls_encoder = None
        try:
            if config.STREAM_SOURCE_TYPE == "webcam":
                streamer = WebcamStreamer(int(stream_source), width, height)
            else:
                streamer = FFmpegStreamer(str(stream_source), width, height)
            streamer.start()

            system_status.set_camera_status(True)
            system_status.set_streamer_status(True)
            print("[Main] Camera stream connected")

            if config.OUTPUT_MODE == "hls" and encoder:
                encoder.start()

            frame_count = 0
            while True:
                frame = streamer.get_frame()
                if frame is None:
                    break

                annotated_frame, detections = detector.detect_with_info(frame)

                if config.OUTPUT_MODE == "sse":
                    encoded_frame = sse_encoder.encode_frame(annotated_frame)
                    if encoded_frame:
                        frame_queue.put(encoded_frame, timeout=0.1)
                else:
                    if encoder:
                        encoder.write_frame(annotated_frame)

                violation_types_found = {}
                for detection in detections:
                    class_name = detection["class_name"]
                    if class_name.startswith("no-"):
                        violation_types_found[class_name] = detection

                for violation_type, detection_info in violation_types_found.items():
                    violation_code = map_violation_class_name_to_code(violation_type)
                    violation_submitter.add_violation(
                        annotated_frame, violation_type, violation_code
                    )

                current_time = time.time()
                if current_time - last_heartbeat_time >= heartbeat_interval:
                    print(
                        f"[Main] Heartbeat: Active for {int(current_time - last_heartbeat_time)}s"
                    )
                    print(
                        f"[Main] Status - YOLO: {system_status.yolo_status}, Camera: {system_status.camera_status}, Streamer: {system_status.streamer_status}"
                    )
                    last_heartbeat_time = current_time

                frame_count += 1

                if frame_count % 100 == 0:
                    print(f"[Main] Processed {frame_count} frames")

                    if config.OUTPUT_MODE == "hls" and hls_manager:
                        if not hls_manager.is_playlist_valid(config.OUTPUT_FILE):
                            print(f"[Main] Warning: Playlist is invalid")

                if cv2.waitKey(1) & 0xFF == ord(config.QUIT_KEY):
                    streamer.stop()
                    if encoder:
                        encoder.stop()
                    violation_submitter.stop()
                    cv2.destroyAllWindows()
                    exit()

        except KeyboardInterrupt:
            print("\n[Main] Stopping...")
            violation_submitter.stop()
            break
        except Exception as e:
            system_status.set_camera_status(False)
            system_status.set_streamer_status(False)
            print(f"[Main] Error: {e}")
            print("[Main] Restarting in 3 seconds...")
            time.sleep(3)
        finally:
            if streamer:
                streamer.stop()
            if encoder:
                encoder.stop()
            violation_submitter.stop()
            cv2.destroyAllWindows()


if __name__ == "__main__":
    main()
