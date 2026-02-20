import type { PredictResult } from "./predict-result";

export interface ReportStream {
	base_response?: BaseResponse;
	profile_name?: string;
	total_battery?: number;
	total_good?: number;
	total_not_good?: number;
	start_time?: string;
	shift_name?: string;
	shift_start_time?: string;
	shift_end_time?: string;
	is_active?: boolean;
	is_processing?: boolean;
	detection_result?: PredictResult | null;
	report_id?: string;
}

export interface BaseResponse {
	validation_errors?: string[];
	status_code?: string;
	message?: string;
	is_error?: boolean;
}
