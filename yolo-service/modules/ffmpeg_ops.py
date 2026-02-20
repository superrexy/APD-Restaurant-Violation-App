"""FFmpeg Operations Module"""

import subprocess
import cv2
import numpy as np
import threading
import config


class FFmpegStreamer:
    """Read stream from URL and output raw frames"""

    def __init__(self, url: str, width: int, height: int):
        """Initialize FFmpeg streamer

        Args:
            url: Stream URL to read from
            width: Video width in pixels
            height: Video height in pixels
        """
        self.url = url
        self.width = width
        self.height = height
        self.frame_size = width * height * 3
        self.process = None

    def start(self) -> subprocess.Popen:
        """Start FFmpeg stream reader process

        Returns:
            Popen object with stdout.PIPE
        """
        command = [
            "ffmpeg",
            "-protocol_whitelist",
            config.FFMPEG_PROTOCOL_WHITELIST,
            "-i",
            self.url,
            "-loglevel",
            config.FFMPEG_LOGLEVEL,
            "-an",
            "-f",
            "rawvideo",
            "-pix_fmt",
            "bgr24",
            "-vsync",
            "0",
            "-fflags",
            "nobuffer",
            "-flags",
            "low_delay",
            "pipe:1",
        ]
        self.process = subprocess.Popen(
            command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, bufsize=10**9
        )
        return self.process

    def get_frame(self) -> np.ndarray:
        """Read one frame from stream

        Returns:
            numpy array (height, width, 3) or None if stream ended
        """
        if not self.process or not self.process.stdout:
            return None

        raw_frame = self.process.stdout.read(self.frame_size)

        if len(raw_frame) == 0 or len(raw_frame) != self.frame_size:
            return None

        frame = np.frombuffer(raw_frame, dtype=np.uint8).reshape(
            (self.height, self.width, 3)
        )
        return frame

    def stop(self):
        """Gracefully stop stream process"""
        if self.process:
            self.process.terminate()
            try:
                self.process.wait(timeout=5)
            except:
                self.process.kill()


class FFmpegHLSEncoder:
    """Encode raw frames to HLS format"""

    def __init__(
        self,
        width: int,
        height: int,
        fps: float,
        output_file: str,
        hls_time: int,
        list_size: int,
        delete_threshold: int,
    ):
        """Initialize FFmpeg HLS encoder

        Args:
            width: Video width in pixels
            height: Video height in pixels
            fps: Frames per second
            output_file: Output m3u8 file path
            hls_time: Segment duration in seconds
            list_size: Number of segments in playlist
            delete_threshold: Segments to keep before deletion
        """
        self.width = width
        self.height = height
        self.fps = fps
        self.output_file = output_file
        self.hls_time = hls_time
        self.list_size = list_size
        self.delete_threshold = delete_threshold
        self.process = None

    def start(self) -> subprocess.Popen:
        """Start FFmpeg HLS encoder process

        Returns:
            Popen object with stdin.PIPE
        """
        command = [
            "ffmpeg",
            "-f",
            "rawvideo",
            "-pix_fmt",
            "bgr24",
            "-s",
            f"{self.width}x{self.height}",
            "-r",
            str(self.fps),
            "-i",
            "-",
            "-c:v",
            "libx264",
            "-preset",
            "veryfast",
            "-tune",
            "zerolatency",
            "-profile:v",
            "baseline",
            "-level",
            "3.0",
            "-pix_fmt",
            "yuv420p",
            "-g",
            str(int(self.fps * 2)),
            "-sc_threshold",
            "0",
            "-hls_time",
            str(self.hls_time),
            "-hls_list_size",
            str(self.list_size),
            "-hls_flags",
            "delete_segments",
            "-hls_delete_threshold",
            str(self.delete_threshold),
            "-hls_segment_filename",
            f"{config.OUTPUT_DIR}/stream%d.ts",
            "-f",
            "hls",
            self.output_file,
        ]

        self.process = subprocess.Popen(
            command, stdin=subprocess.PIPE, stderr=subprocess.PIPE
        )

        def log_ffmpeg_errors():
            for line in iter(self.process.stderr.readline, b""):
                if line:
                    print(
                        f"[FFmpeg Encoder] {line.decode('utf-8', errors='ignore').strip()}"
                    )

        thread = threading.Thread(target=log_ffmpeg_errors, daemon=True)
        thread.start()

        return self.process

    def write_frame(self, frame: np.ndarray):
        """Write frame to encoder stdin

        Args:
            frame: numpy array (height, width, 3)
        """
        if self.process and self.process.stdin:
            self.process.stdin.write(frame.tobytes())

    def stop(self):
        """Gracefully stop encoder"""
        if self.process:
            if self.process.stdin:
                try:
                    self.process.stdin.close()
                except:
                    pass
            self.process.terminate()
            try:
                self.process.wait(timeout=5)
            except:
                self.process.kill()


class StreamInfo:
    """Utility for probing stream information"""

    @staticmethod
    def get_dimensions(url: str, timeout: int = None) -> tuple[int, int]:
        """Get stream width and height using ffprobe

        Args:
            url: Stream URL to probe
            timeout: Timeout in seconds (uses config default if None)

        Returns:
            (width, height) or default (1280, 720)
        """
        timeout = timeout or config.FFMPEG_TIMEOUT
        try:
            cmd = [
                "ffprobe",
                "-protocol_whitelist",
                config.FFMPEG_PROTOCOL_WHITELIST,
                "-v",
                "error",
                "-select_streams",
                "v:0",
                "-show_entries",
                "stream=width,height",
                "-of",
                "csv=s=x:p=0",
                url,
            ]
            result = subprocess.run(
                cmd, capture_output=True, text=True, timeout=timeout
            )
            if result.returncode == 0 and result.stdout.strip():
                dimensions_line = result.stdout.strip().split("\n")[0]
                parts = dimensions_line.split(",")
                if len(parts) == 1:
                    width, height = map(int, parts[0].split("x"))
                else:
                    width, height = map(int, parts[0].split("x"))
                return width, height
        except:
            pass
        return 1280, 720

    @staticmethod
    def get_fps(url: str, timeout: int = None) -> float:
        """Get stream FPS using ffprobe

        Args:
            url: Stream URL to probe
            timeout: Timeout in seconds (uses config default if None)

        Returns:
            FPS or default 25.0
        """
        timeout = timeout or config.FFMPEG_TIMEOUT
        try:
            cmd = [
                "ffprobe",
                "-protocol_whitelist",
                config.FFMPEG_PROTOCOL_WHITELIST,
                "-v",
                "error",
                "-select_streams",
                "v:0",
                "-show_entries",
                "stream=r_frame_rate",
                "-of",
                "csv=s=x:p=0",
                url,
            ]
            result = subprocess.run(
                cmd, capture_output=True, text=True, timeout=timeout
            )
            if result.returncode == 0 and result.stdout.strip():
                fps_str = result.stdout.strip().split("\n")[0].split(",")[0]
                fps_num, fps_den = map(int, fps_str.split("/"))
                return fps_num / fps_den
        except:
            pass
        return 25.0


class WebcamStreamer:
    """Direct webcam capture using OpenCV"""

    def __init__(self, device_index: int, width: int, height: int):
        """Initialize webcam streamer

        Args:
            device_index: Webcam device index (0, 1, 2...)
            width: Desired width in pixels
            height: Desired height in pixels
        """
        self.device_index = device_index
        self.width = width
        self.height = height
        self.cap = None

    def start(self):
        """Start webcam capture"""
        self.cap = cv2.VideoCapture(self.device_index)
        if not self.cap.isOpened():
            raise RuntimeError(f"Failed to open webcam device {self.device_index}")

        self.cap.set(cv2.CAP_PROP_FRAME_WIDTH, self.width)
        self.cap.set(cv2.CAP_PROP_FRAME_HEIGHT, self.height)
        return self.cap

    def get_frame(self) -> np.ndarray:
        """Read one frame from webcam

        Returns:
            numpy array (height, width, 3) or None if failed
        """
        if not self.cap or not self.cap.isOpened():
            return None

        ret, frame = self.cap.read()
        if not ret:
            return None

        return frame

    def stop(self):
        """Stop webcam capture"""
        if self.cap:
            self.cap.release()
            self.cap = None
