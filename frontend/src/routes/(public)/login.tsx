import { createFileRoute, redirect } from "@tanstack/react-router";
import LoginPage from "../../features/LoginPage";
import { useAuthStore } from "../../store/use-auth";

export const Route = createFileRoute("/(public)/login")({
	beforeLoad: () => {
		const isAuthenticated = useAuthStore.getState().isAuthenticated;
		if (isAuthenticated) {
			throw redirect({ to: "/" });
		}
	},
	component: LoginPage,
});
