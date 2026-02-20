import { createFileRoute, Outlet, redirect } from "@tanstack/react-router";
import MainLayout from "../../layouts/MainLayout";
import { useAuthStore } from "../../store/use-auth";

export const Route = createFileRoute("/(dashboard)")({
	beforeLoad: () => {
		const isAuthenticated = useAuthStore.getState().isAuthenticated;
		if (!isAuthenticated) {
			throw redirect({ to: "/login" });
		}
	},
	component: RouteComponent,
});

function RouteComponent() {
	return (
		<MainLayout>
			<Outlet />
		</MainLayout>
	);
}
