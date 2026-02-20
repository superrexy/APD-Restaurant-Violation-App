import {
	IconAlertCircle,
	IconDashboard,
	IconUserCheck,
} from "@tabler/icons-react";

export const sidebar = {
	user: {
		name: "shadcn",
		email: "m@example.com",
		avatar: "/avatars/shadcn.jpg",
	},
	navMain: [
		{
			title: "Dashboard",
			url: "/",
			icon: IconDashboard,
		},
		{
			title: "Violation History",
			url: "/violation-history",
			icon: IconAlertCircle,
		},
		{
			title: "User Management",
			url: "/user-management",
			icon: IconUserCheck,
		},
	],
};
