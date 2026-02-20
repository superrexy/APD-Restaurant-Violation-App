import axiosInstance from "../config/axios";

export const fetcher = <T>(url: string) =>
	axiosInstance.get<T>(url).then((res) => res.data);
