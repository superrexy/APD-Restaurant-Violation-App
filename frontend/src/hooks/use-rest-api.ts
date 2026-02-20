import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";
import { useCallback } from "react";
import { toast } from "sonner";
import type { BaseResponse } from "@/api/rest/client";

interface CallAPIArgs {
	useDefaultError?: boolean;
	defaultError?: (error: unknown) => void;
}

function useRestApi() {
	const navigate = useNavigate();

	const callAPI = useCallback(
		async <T>(
			apiCall: () => Promise<BaseResponse<T>>,
			args: CallAPIArgs = {},
		): Promise<BaseResponse<T> | undefined> => {
			try {
				const response = await apiCall();
				return response;
			} catch (error) {
				const axiosError = error as AxiosError<BaseResponse>;

				if (axiosError.response?.status === 401) {
					toast.error("Session expired, please login again");
					navigate({ to: "/login" });
					return undefined;
				}

				if (args?.defaultError) {
					args.defaultError(error);
				}

				if (args?.useDefaultError ?? true) {
					const errorMessage =
						axiosError.response?.data?.message ||
						axiosError.message ||
						"Something went wrong, please try again";
					toast.error(errorMessage);
				}

				throw error;
			}
		},
		[navigate],
	);

	return {
		callAPI,
	};
}

export default useRestApi;
