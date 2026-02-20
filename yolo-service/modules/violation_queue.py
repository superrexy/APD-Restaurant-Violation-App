"""Violation Queue Module for Throttling Submissions"""

import time
import threading
from typing import Optional
import config


class ViolationQueue:
    def __init__(self, delay_seconds: int = None):
        self.delay = delay_seconds or config.VIOLATION_DELAY
        self._last_submissions: dict = {}
        self._lock = threading.Lock()

    def can_submit(self, violation_type: str) -> bool:
        current_time = time.time()

        with self._lock:
            last_time = self._last_submissions.get(violation_type, 0)
            if current_time - last_time >= self.delay:
                self._last_submissions[violation_type] = current_time
                return True
            return False

    def get_remaining_time(self, violation_type: str) -> float:
        current_time = time.time()

        with self._lock:
            last_time = self._last_submissions.get(violation_type, 0)
            elapsed = current_time - last_time
            return max(0.0, self.delay - elapsed)

    def reset(self, violation_type: Optional[str] = None):
        with self._lock:
            if violation_type:
                self._last_submissions.pop(violation_type, None)
            else:
                self._last_submissions.clear()
