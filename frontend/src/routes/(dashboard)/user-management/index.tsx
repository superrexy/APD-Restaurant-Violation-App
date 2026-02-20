import { createFileRoute } from "@tanstack/react-router";
import UserManagementPage from "../../../features/UserManagementPage";

export const Route = createFileRoute("/(dashboard)/user-management/")({
	component: UserManagementPage,
});
