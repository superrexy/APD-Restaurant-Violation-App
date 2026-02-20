import { useCallback, useEffect, useState } from "react";
import { violationsApi, type Violation } from "../api/rest/client";
import useRestApi from "./use-rest-api";

interface UseViolationsPollingOptions {
  pollingInterval?: number;
  enabled?: boolean;
}

export const useViolationsPolling = (
  options: UseViolationsPollingOptions = {},
) => {
  const { pollingInterval = 3000, enabled = true } = options;
  const { callAPI } = useRestApi();
  const [violations, setViolations] = useState<Violation[]>([]);
  const [isPolling, setIsPolling] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchViolations = useCallback(async () => {
    try {
      setError(null);
      const response = await callAPI(() =>
        violationsApi.list({
          page: 1,
          per_page: 5,
          sort_by: "created_at",
          sort_order: "desc",
        }),
      );

      if (response?.data) {
        setViolations(response.data);
      }
    } catch {
      setError("Failed to fetch violations");
    }
  }, [callAPI]);

  useEffect(() => {
    if (!enabled) {
      return;
    }

    setIsPolling(true);
    fetchViolations();

    const interval = setInterval(() => {
      fetchViolations();
    }, pollingInterval);

    return () => {
      clearInterval(interval);
      setIsPolling(false);
    };
  }, [pollingInterval, enabled, fetchViolations]);

  return { violations, isPolling, error };
};
