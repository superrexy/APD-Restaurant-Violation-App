export interface Health {
	status: "ok" | "error";
	mode: "sse" | "hls";
	active_clients: number;
	camera_status: boolean;
	yolo_status: boolean;
	streamer_status: boolean;
	source_type: "url" | "webcam" | "file";
	uptime_seconds: number;
}
