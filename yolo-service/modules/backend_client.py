"""Backend API Client for violation reporting"""

import httpx
import json
from typing import Optional, Dict, Any
import logging
import config

logger = logging.getLogger(__name__)


class BackendClient:
    """HTTP client for backend API communication"""

    def __init__(
        self,
        api_url: Optional[str] = None,
        api_key: Optional[str] = None,
        camera_code: Optional[str] = None,
        timeout: float = 30.0,
    ):
        """Initialize backend client

        Args:
            api_url: Backend API base URL (default: from config)
            api_key: API key for authentication (default: from config)
            camera_code: Camera identifier (default: from config)
            timeout: Request timeout in seconds
        """
        self.api_url = api_url or config.BACKEND_API_URL
        self.api_key = api_key or config.BACKEND_API_KEY
        self.camera_code = camera_code or config.CAMERA_CODE
        self.timeout = timeout

        self.client = httpx.AsyncClient(
            base_url=self.api_url,
            headers=self._get_headers(),
            timeout=self.timeout,
        )

    def _get_headers(self) -> Dict[str, str]:
        """Build request headers with API key

        Returns:
            Dictionary of HTTP headers
        """
        headers = {
            "Accept": "application/json",
        }

        if self.api_key:
            headers["X-API-Key"] = self.api_key

        return headers

    async def submit_violation(
        self,
        image_path: str,
        violation_details: list[dict],
        notes: Optional[str] = None,
    ) -> Dict[str, Any]:
        """Submit violation report to backend

        Args:
            image_path: Path to violation image file
            violation_details: List of violation detail objects with structure:
                [{"violation_code": "NO_APRON", "confidence_score": 0.95, ...}]
            notes: Optional notes/observations

        Returns:
            Response dictionary with status and data

        Raises:
            httpx.HTTPError: On HTTP request failure
            FileNotFoundError: If image file not found
        """
        image_file = None
        try:
            image_file = open(image_path, "rb")
            files = {"image": (image_path.split("/")[-1], image_file, "image/jpeg")}

            data = {
                "camera_code": self.camera_code,
            }

            if notes is not None:
                data["notes"] = notes

            for idx, detail in enumerate(violation_details):
                for key, value in detail.items():
                    if value is not None:
                        data[f"violation_details[{idx}][{key}]"] = value

            response = await self.client.post(
                "/api/violations",
                files=files,
                data=data,
            )

            response.raise_for_status()

            result = response.json()
            logger.info(
                f"Violation submitted successfully: {result.get('data', {}).get('id')}"
            )
            return result

        except httpx.HTTPStatusError as e:
            logger.error(
                f"HTTP error submitting violation: {e.response.status_code} - {e.response.text}"
            )
            raise
        except FileNotFoundError:
            logger.error(f"Image file not found: {image_path}")
            raise
        except Exception as e:
            logger.error(f"Error submitting violation: {e}")
            raise
        finally:
            if image_file:
                image_file.close()

    async def get_camera(self, camera_id: int) -> Dict[str, Any]:
        """Fetch camera details from backend

        Args:
            camera_id: Camera ID to fetch

        Returns:
            Camera data dictionary

        Raises:
            httpx.HTTPError: On HTTP request failure
        """
        response = await self.client.get(f"/api/cameras/{camera_id}")
        response.raise_for_status()
        return response.json()

    async def get_violation_types(self) -> Dict[str, Any]:
        """Fetch available violation types from backend

        Returns:
            List of violation types

        Raises:
            httpx.HTTPError: On HTTP request failure
        """
        response = await self.client.get("/api/violation-types")
        response.raise_for_status()
        return response.json()

    async def health_check(self) -> bool:
        """Check if backend API is reachable

        Returns:
            True if backend is healthy, False otherwise
        """
        try:
            response = await self.client.get("/health")
            return response.status_code == 200
        except Exception as e:
            logger.warning(f"Backend health check failed: {e}")
            return False

    async def close(self):
        """Close the HTTP client"""
        await self.client.aclose()
        logger.info("Backend client closed")

    async def __aenter__(self):
        """Async context manager entry"""
        return self

    async def __aexit__(self, exc_type, exc_val, exc_tb):
        """Async context manager exit"""
        await self.close()
