export interface PredictResult {
	status?: string;
	message?: string;
	total_objects?: number;
	roi_counts?: { [key: string]: number };
	roi_statuses?: { [key: string]: RoiStatus };
	overall_status?: Status;
	result_image?: string;
	timestamp?: Date;
	detailed_results?: DetailedResult[];
	roi_total?: number;
	detection_summary?: DetectionSummary;
	metadata?: Metadata;
	saved_json_path?: string;
}

export interface DetailedResult {
	roi_id?: number;
	label?: Label;
	confidence?: number;
	bbox_original?: number[];
	bbox_roi_upscaled?: number[];
}

export enum Label {
	Plate = "plate",
}

export interface DetectionSummary {
	good_rois?: number;
	not_good_rois?: number;
	total_rois?: number;
	total_detected_objects?: number;
}

export interface Metadata {
	timestamp?: Date;
	filename?: string;
}

export enum Status {
	Good = "GOOD",
	NotGood = "NOT GOOD",
}

export interface RoiStatus {
	status?: Status;
	message?: string;
	expected?: number;
	detected?: number;
}
