"""YOLO Object Detection Module"""

import numpy as np
from ultralytics import YOLO
import config


class YOLODetector:
    """YOLO object detector"""

    def __init__(
        self, model_path: str = None, device: str = None, classes: list = None
    ):
        """Initialize YOLO model

        Args:
            model_path: Path to YOLO model file
            device: Device to run on (mps, cuda, cpu)
            classes: List of class IDs to detect
        """
        model_path = model_path or config.YOLO_MODEL_PATH
        device = device or config.YOLO_DEVICE
        classes = classes or config.YOLO_CLASSES

        self.model = YOLO(model_path)
        self.device = device
        self.classes = classes

    def detect(self, frame: np.ndarray) -> np.ndarray:
        """Run detection and return annotated frame

        Args:
            frame: Input frame (height, width, 3)

        Returns:
            Annotated frame with bounding boxes
        """
        results = self.model(
            frame, device=self.device, verbose=False, classes=self.classes
        )[0]
        annotated_frame = results.plot()
        return annotated_frame

    def detect_with_info(self, frame: np.ndarray) -> tuple[np.ndarray, list[dict]]:
        """Run detection and return annotated frame with detection info

        Args:
            frame: Input frame (height, width, 3)

        Returns:
            Tuple of (annotated_frame, detections) where detections is a list of
            dicts with keys: class_id, class_name, confidence, bbox
        """
        results = self.model(
            frame, device=self.device, verbose=False, classes=self.classes
        )[0]
        annotated_frame = results.plot()

        detections = []
        if results.boxes is not None:
            for box in results.boxes:
                detections.append(
                    {
                        "class_id": int(box.cls[0]),
                        "class_name": self.model.names[int(box.cls[0])],
                        "confidence": float(box.conf[0]),
                        "bbox": box.xyxy[0].tolist(),
                    }
                )

        return annotated_frame, detections
