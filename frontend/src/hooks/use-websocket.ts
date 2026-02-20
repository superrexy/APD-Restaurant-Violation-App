import { useCallback, useEffect, useRef, useState } from "react";
import { toast } from "sonner";

export interface WebSocketOptions {
	url: string;
	protocols?: string | string[];
	reconnectInterval?: number;
	maxReconnectAttempts?: number;
	onOpen?: (event: Event) => void;
	onClose?: (event: CloseEvent) => void;
	onError?: (event: Event) => void;
	onMessage?: (event: MessageEvent) => void;
	autoConnect?: boolean;
	showErrorToast?: boolean;
}

export interface WebSocketState {
	isConnected: boolean;
	isConnecting: boolean;
	error: string | null;
	lastMessage: MessageEvent | null;
}

export function useWebSocket<T = unknown>(options: WebSocketOptions) {
	const {
		url,
		protocols,
		reconnectInterval = 3000,
		maxReconnectAttempts = 5,
		onOpen,
		onClose,
		onError,
		onMessage,
		autoConnect = true,
		showErrorToast = true,
	} = options;

	const [state, setState] = useState<WebSocketState>({
		isConnected: false,
		isConnecting: false,
		error: null,
		lastMessage: null,
	});

	const wsRef = useRef<WebSocket | null>(null);
	const reconnectTimeoutRef = useRef<NodeJS.Timeout | null>(null);
	const reconnectAttemptsRef = useRef(0);
	const shouldReconnectRef = useRef(true);
	const isConnectingRef = useRef(false);

	const connect = useCallback(() => {
		if (
			wsRef.current?.readyState === WebSocket.OPEN ||
			wsRef.current?.readyState === WebSocket.CONNECTING ||
			isConnectingRef.current
		) {
			return;
		}

		// Clean up existing connection first
		if (wsRef.current) {
			wsRef.current.close();
			wsRef.current = null;
		}

		isConnectingRef.current = true;
		setState((prev) => ({ ...prev, isConnecting: true, error: null }));

		try {
			const ws = new WebSocket(url, protocols);
			wsRef.current = ws;

			ws.onopen = (event) => {
				isConnectingRef.current = false;
				setState((prev) => ({
					...prev,
					isConnected: true,
					isConnecting: false,
					error: null,
				}));
				reconnectAttemptsRef.current = 0;
				onOpen?.(event);
			};

			ws.onclose = (event) => {
				isConnectingRef.current = false;
				setState((prev) => ({
					...prev,
					isConnected: false,
					isConnecting: false,
				}));
				onClose?.(event);

				// Only attempt to reconnect if not manually closed and not a clean close
				if (
					shouldReconnectRef.current &&
					reconnectAttemptsRef.current < maxReconnectAttempts &&
					event.code !== 1000 // Not a normal closure
				) {
					reconnectAttemptsRef.current++;
					console.log(
						`WebSocket reconnecting... attempt ${reconnectAttemptsRef.current}/${maxReconnectAttempts}`,
					);
					reconnectTimeoutRef.current = setTimeout(() => {
						connect();
					}, reconnectInterval);
				} else if (reconnectAttemptsRef.current >= maxReconnectAttempts) {
					setState((prev) => ({
						...prev,
						error: `Failed to reconnect after ${maxReconnectAttempts} attempts`,
					}));
					if (showErrorToast) {
						toast.error(
							"WebSocket connection failed. Please refresh the page.",
						);
					}
				}
			};

			ws.onerror = (event) => {
				isConnectingRef.current = false;
				setState((prev) => ({
					...prev,
					error: "WebSocket connection error",
					isConnecting: false,
				}));
				onError?.(event);
				if (showErrorToast) {
					toast.error("WebSocket connection error");
				}
			};

			ws.onmessage = (event) => {
				setState((prev) => ({
					...prev,
					lastMessage: event,
				}));
				onMessage?.(event);
			};
		} catch (error) {
			isConnectingRef.current = false;
			setState((prev) => ({
				...prev,
				error: `Failed to create WebSocket connection: ${error}`,
				isConnecting: false,
			}));
			if (showErrorToast) {
				toast.error("Failed to create WebSocket connection");
			}
		}
	}, [
		url,
		protocols,
		reconnectInterval,
		maxReconnectAttempts,
		onOpen,
		onClose,
		onError,
		onMessage,
		showErrorToast,
	]);

	const disconnect = useCallback(() => {
		shouldReconnectRef.current = false;

		if (reconnectTimeoutRef.current) {
			clearTimeout(reconnectTimeoutRef.current);
			reconnectTimeoutRef.current = null;
		}

		if (wsRef.current) {
			// Only close if not already closed
			if (
				wsRef.current.readyState === WebSocket.OPEN ||
				wsRef.current.readyState === WebSocket.CONNECTING
			) {
				wsRef.current.close();
			}
			wsRef.current = null;
		}

		setState((prev) => ({
			...prev,
			isConnected: false,
			isConnecting: false,
		}));
	}, []);

	const sendMessage = useCallback(
		(message: string | ArrayBufferLike | Blob | ArrayBufferView) => {
			if (wsRef.current?.readyState === WebSocket.OPEN) {
				wsRef.current.send(message);
				return true;
			}
			return false;
		},
		[],
	);

	const sendJsonMessage = useCallback(
		(data: T) => {
			return sendMessage(JSON.stringify(data));
		},
		[sendMessage],
	);

	// Auto-connect on mount
	useEffect(() => {
		if (autoConnect) {
			// Add small delay to ensure component is fully mounted
			const connectTimeout = setTimeout(() => {
				connect();
			}, 100);

			return () => {
				clearTimeout(connectTimeout);
				disconnect();
			};
		}

		return () => {
			disconnect();
		};
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [autoConnect, connect, disconnect]);

	// Cleanup on unmount
	useEffect(() => {
		return () => {
			shouldReconnectRef.current = false;
			if (reconnectTimeoutRef.current) {
				clearTimeout(reconnectTimeoutRef.current);
				reconnectTimeoutRef.current = null;
			}
			if (wsRef.current) {
				// Only close if not already closed
				if (
					wsRef.current.readyState === WebSocket.OPEN ||
					wsRef.current.readyState === WebSocket.CONNECTING
				) {
					wsRef.current.close();
				}
				wsRef.current = null;
			}
		};
	}, []);

	return {
		...state,
		connect,
		disconnect,
		sendMessage,
		sendJsonMessage,
		ws: wsRef.current,
	};
}

export default useWebSocket;
