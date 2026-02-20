import { useCallback, useEffect, useState } from "react";

interface HealthStatus {
  status: string;
  camera_status: boolean;
  yolo_status: boolean;
  streamer_status: boolean;
  uptime_seconds: number;
  active_clients: number;
  source_type: string;
}

export function useHealthPolling(pollingInterval: number = 1000) {
  const [status, setStatus] = useState<HealthStatus | null>(null);
  const [isPolling, setIsPolling] = useState(false);

  const fetchHealth = useCallback(async () => {
    try {
      const response = await fetch(
        `${import.meta.env.VITE_YOLO_SERVICE_URL}/health`,
      );
      const data = await response.json();
      setStatus(data);
    } catch (err) {
      setStatus(null);
      console.error("Health polling error:", err);
    }
  }, []);

  useEffect(() => {
    fetchHealth();
    setIsPolling(true);

    const interval = setInterval(fetchHealth, pollingInterval);

    return () => {
      clearInterval(interval);
      setIsPolling(false);
    };
  }, [pollingInterval, fetchHealth]);

  return { status, isPolling };
}
