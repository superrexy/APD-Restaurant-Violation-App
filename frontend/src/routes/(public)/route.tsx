import { createFileRoute, Outlet, redirect } from "@tanstack/react-router";
import { useAuthStore } from "../../store/use-auth";

export const Route = createFileRoute("/(public)")({
	component: RouteComponent,
	beforeLoad: () => {
		const isAuthenticated = useAuthStore.getState().isAuthenticated;
		if (isAuthenticated) {
			throw redirect({
				to: "/",
			});
		}
	},
});

function RouteComponent() {
	return (
		<>
			<Outlet />
		</>
	);
}
