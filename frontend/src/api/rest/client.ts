import { useAuthStore } from "@/store/use-auth";
import axios, { type AxiosInstance } from "axios";

// ============================================
// Base Response Types (from backend Controller.php)
// ============================================

export interface BaseResponse<T = unknown> {
	statusCode: number;
	message: string;
	data: T;
	meta: Record<string, unknown>;
}

export interface PaginatedMeta extends Record<string, unknown> {
	current_page: number;
	per_page: number;
	total: number;
	last_page: number;
}

export interface PaginatedResponse<T> extends BaseResponse<T[]> {
	meta: PaginatedMeta;
}

// ============================================
// Entity Types (from backend Models)
// ============================================

export interface User {
	id: number;
	name: string;
	email: string;
	created_at: string;
	updated_at: string;
}

export interface Camera {
	id: number;
	name: string;
	description: string | null;
	location: string | null;
	code: string;
	status: string;
	connected_at: string | null;
	disconnected_at: string | null;
	last_maintenance_at: string | null;
}

export interface ViolationType {
	id: number;
	name: string;
	description: string | null;
	code: string;
	severity: "low" | "medium" | "high";
	is_active: boolean;
	created_at: string;
	updated_at: string;
}

export interface ViolationDetail {
	id: number;
	violation_id: number;
	violation_type_id: number;
	confidence_score: number | null;
	additional_info: string | null;
	status: "unverified" | "confirmed" | "dismissed";
	violation?: Violation;
	violation_type?: ViolationType;
}

export interface Violation {
	id: number;
	camera_id: number;
	image_path: string;
	status: "pending" | "reviewed" | "resolved";
	notes: string | null;
	created_at: string;
	updated_at: string;
	camera?: Camera;
	violation_details?: ViolationDetail[];
}

// ============================================
// Request Types (from backend FormRequests)
// ============================================

export interface LoginRequest {
	email: string;
	password: string;
}

export interface UserStoreRequest {
	name: string;
	email: string;
	password: string;
}

export interface UserUpdateRequest {
	name: string;
	email: string;
	password?: string;
}

export interface ViolationStoreRequest {
	image: File;
	camera_code: string;
	notes?: string;
	violation_details: Array<{
		violation_code: string;
		confidence_score?: number;
		additional_info?: string;
	}>;
}

export interface ViolationUpdateStatusRequest {
	status: "pending" | "reviewed" | "resolved";
	notes?: string;
}

export interface ViolationDetailUpdateStatusRequest {
	status: "unverified" | "confirmed" | "dismissed";
	additional_info?: string;
}

export interface ViolationTypeStoreRequest {
	name: string;
	description?: string;
	code: string;
	severity: "low" | "medium" | "high";
	is_active: boolean;
}

export interface ViolationTypeUpdateRequest {
	name?: string;
	description?: string;
	code?: string;
	severity?: "low" | "medium" | "high";
	is_active?: boolean;
}

// ============================================
// Auth Response Types
// ============================================

export interface AuthResponse {
	token: string;
	user: User;
}

// ============================================
// Query Params Types
// ============================================

export interface PaginationParams {
	page?: number;
	per_page?: number;
}

export interface ViolationListParams extends PaginationParams {
	status?: string | undefined;
	sort_by?: string | undefined;
	sort_order?: "asc" | "desc" | undefined;
}

// ============================================
// Axios Client Setup
// ============================================

const BASE_URL = import.meta.env.VITE_BASE_API_URL || "http://localhost:8000";

const createApiClient = (): AxiosInstance => {
	const instance = axios.create({
		baseURL: `${BASE_URL}/api`,
		headers: {
			"Content-Type": "application/json",
			Accept: "application/json",
		},
		withCredentials: true,
	});

	// Request interceptor - add auth token
	instance.interceptors.request.use(
		(config) => {
			const token = useAuthStore.getState().token;
			if (token) {
				config.headers.Authorization = `Bearer ${token}`;
			}
			return config;
		},
		(error) => Promise.reject(error),
	);

	instance.interceptors.response.use(
		(response) => response,
		(error) => {
			if (error.response?.status === 401) {
				// Only logout and redirect if NOT on login page
				if (typeof window !== 'undefined' && window.location.pathname !== '/login') {
					const { logout } = useAuthStore.getState();
					logout();
					window.location.href = '/login';
				}
			}
			return Promise.reject(error);
		},
	);

	return instance;
};

const apiClient = createApiClient();

// ============================================
// API Service Functions
// ============================================

// ---------- Auth API ----------
export const authApi = {
	login: async (data: LoginRequest): Promise<BaseResponse<AuthResponse>> => {
		const response = await apiClient.post<BaseResponse<AuthResponse>>(
			"/auth/login",
			data,
		);
		return response.data;
	},

	logout: async (): Promise<void> => {
		await apiClient.post("/auth/logout");
	},

	refreshToken: async (): Promise<BaseResponse<AuthResponse>> => {
		const response =
			await apiClient.post<BaseResponse<AuthResponse>>("/auth/refresh");
		return response.data;
	},

	getCurrentUser: async (): Promise<BaseResponse<User>> => {
		const response = await apiClient.get<BaseResponse<User>>("/profile");
		return response.data;
	},
};

// ---------- Users API ----------
export interface UserListParams extends PaginationParams {
	name?: string;
	email?: string;
}

export const usersApi = {
	list: async (params?: UserListParams): Promise<PaginatedResponse<User>> => {
		const response = await apiClient.get<PaginatedResponse<User>>("/users", {
			params,
		});
		return response.data;
	},

	get: async (id: number): Promise<BaseResponse<User>> => {
		const response = await apiClient.get<BaseResponse<User>>(`/users/${id}`);
		return response.data;
	},

	create: async (data: UserStoreRequest): Promise<BaseResponse<User>> => {
		const response = await apiClient.post<BaseResponse<User>>("/users", data);
		return response.data;
	},

	update: async (
		id: number,
		data: UserUpdateRequest,
	): Promise<BaseResponse<User>> => {
		const response = await apiClient.put<BaseResponse<User>>(
			`/users/${id}`,
			data,
		);
		return response.data;
	},

	delete: async (id: number): Promise<void> => {
		await apiClient.delete(`/users/${id}`);
	},
};

// ---------- Violations API ----------
export const violationsApi = {
	list: async (
		params?: ViolationListParams,
	): Promise<PaginatedResponse<Violation>> => {
		const response = await apiClient.get<PaginatedResponse<Violation>>(
			"/violations",
			{ params },
		);
		return response.data;
	},

	get: async (id: number): Promise<BaseResponse<Violation>> => {
		const response = await apiClient.get<BaseResponse<Violation>>(
			`/violations/${id}`,
		);
		return response.data;
	},

	create: async (
		data: ViolationStoreRequest,
	): Promise<BaseResponse<Violation>> => {
		const formData = new FormData();
		formData.append("image", data.image);
		formData.append("camera_code", data.camera_code);
		if (data.notes) formData.append("notes", data.notes);
		formData.append(
			"violation_details",
			JSON.stringify(data.violation_details),
		);

		const response = await apiClient.post<BaseResponse<Violation>>(
			"/violations",
			formData,
			{
				headers: { "Content-Type": "multipart/form-data" },
			},
		);
		return response.data;
	},

	updateStatus: async (
		id: number,
		data: ViolationUpdateStatusRequest,
	): Promise<BaseResponse<Violation>> => {
		const response = await apiClient.put<BaseResponse<Violation>>(
			`/violations/${id}/status`,
			data,
		);
		return response.data;
	},

	delete: async (id: number): Promise<void> => {
		await apiClient.delete(`/violations/${id}`);
	},
};

// ---------- Violation Details API ----------
export const violationDetailsApi = {
	list: async (
		params?: PaginationParams,
	): Promise<PaginatedResponse<ViolationDetail>> => {
		const response = await apiClient.get<PaginatedResponse<ViolationDetail>>(
			"/violation-details",
			{ params },
		);
		return response.data;
	},

	get: async (id: number): Promise<BaseResponse<ViolationDetail>> => {
		const response = await apiClient.get<BaseResponse<ViolationDetail>>(
			`/violation-details/${id}`,
		);
		return response.data;
	},

	updateStatus: async (
		id: number,
		data: ViolationDetailUpdateStatusRequest,
	): Promise<BaseResponse<ViolationDetail>> => {
		const response = await apiClient.put<BaseResponse<ViolationDetail>>(
			`/violation-details/${id}/status`,
			data,
		);
		return response.data;
	},

	delete: async (id: number): Promise<void> => {
		await apiClient.delete(`/violation-details/${id}`);
	},
};

// ---------- Violation Types API ----------
export const violationTypesApi = {
	list: async (
		params?: PaginationParams,
	): Promise<PaginatedResponse<ViolationType>> => {
		const response = await apiClient.get<PaginatedResponse<ViolationType>>(
			"/violation-types",
			{ params },
		);
		return response.data;
	},

	get: async (id: number): Promise<BaseResponse<ViolationType>> => {
		const response = await apiClient.get<BaseResponse<ViolationType>>(
			`/violation-types/${id}`,
		);
		return response.data;
	},

	create: async (
		data: ViolationTypeStoreRequest,
	): Promise<BaseResponse<ViolationType>> => {
		const response = await apiClient.post<BaseResponse<ViolationType>>(
			"/violation-types",
			data,
		);
		return response.data;
	},

	update: async (
		id: number,
		data: ViolationTypeUpdateRequest,
	): Promise<BaseResponse<ViolationType>> => {
		const response = await apiClient.put<BaseResponse<ViolationType>>(
			`/violation-types/${id}`,
			data,
		);
		return response.data;
	},

	delete: async (id: number): Promise<void> => {
		await apiClient.delete(`/violation-types/${id}`);
	},
};

// Export the axios instance for custom use cases
export { apiClient };
export default apiClient;
