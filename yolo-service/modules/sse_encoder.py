"""SSE (Server-Sent Events) Encoder Module"""

import cv2
import numpy as np


class SSEncoder:
    """Encode frames for SSE streaming with multipart/x-mixed-replace format"""
    
    def __init__(self, boundary: str = "frame", jpeg_quality: int = 85):
        """Initialize SSE encoder
        
        Args:
            boundary: Boundary string for multipart format
            jpeg_quality: JPEG quality (1-100, higher = better quality)
        """
        self.boundary = boundary
        self.jpeg_quality = jpeg_quality
    
    def encode_frame(self, frame: np.ndarray) -> bytes:
        """Encode frame as JPEG with multipart boundary markers
        
        Args:
            frame: numpy array (height, width, 3)
        
        Returns:
            Encoded frame with boundary markers in multipart format:
            --boundary\r\n
            Content-Type: image/jpeg\r\n
            Content-Length: XXXX\r\n
            \r\n
            [JPEG_DATA]\r\n
        """
        ret, jpeg_buffer = cv2.imencode(
            '.jpg', 
            frame, 
            [int(cv2.IMWRITE_JPEG_QUALITY), self.jpeg_quality]
        )
        
        if not ret:
            return None
        
        jpeg_data = jpeg_buffer.tobytes()
        
        boundary_marker = f"--{self.boundary}\r\n".encode()
        content_type = b"Content-Type: image/jpeg\r\n"
        content_length = f"Content-Length: {len(jpeg_data)}\r\n\r\n".encode()
        
        return boundary_marker + content_type + content_length + jpeg_data + b"\r\n"
