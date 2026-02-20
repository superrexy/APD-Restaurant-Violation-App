import {
	Breadcrumb,
	BreadcrumbItem,
	BreadcrumbLink,
	BreadcrumbList,
	BreadcrumbPage,
	BreadcrumbSeparator,
} from "@/components/ui/breadcrumb";
import { Link, useRouterState } from "@tanstack/react-router";
import React from "react";

const BREADCRUMB_CONFIG: Record<string, string> = {
	"violation-history": "Violation History",
	"user-management": "User Management",
};

export function AppBreadcrumb() {
	const routerState = useRouterState();
	const pathname = routerState.location.pathname;
	const pathSegments = pathname.split("/").filter(Boolean);

	if (pathSegments.length === 0) {
		return (
			<Breadcrumb>
				<BreadcrumbList>
					<BreadcrumbItem>
						<BreadcrumbPage>Dashboard</BreadcrumbPage>
					</BreadcrumbItem>
				</BreadcrumbList>
			</Breadcrumb>
		);
	}

	const breadcrumbItems = pathSegments.map((segment, index) => {
		const path = "/" + pathSegments.slice(0, index + 1).join("/");
		const label = BREADCRUMB_CONFIG[segment] || capitalizeFirstLetter(segment);
		const isLast = index === pathSegments.length - 1;

		return { path, label, isLast };
	});

	return (
		<Breadcrumb>
			<BreadcrumbList>
				<BreadcrumbItem>
					<BreadcrumbLink asChild>
						<Link to="/">Dashboard</Link>
					</BreadcrumbLink>
				</BreadcrumbItem>
				<BreadcrumbSeparator />
				{breadcrumbItems.map((item) => (
					<React.Fragment key={item.path}>
						<BreadcrumbItem>
							{item.isLast ? (
								<BreadcrumbPage>{item.label}</BreadcrumbPage>
							) : (
								<BreadcrumbLink asChild>
									<Link to={item.path}>{item.label}</Link>
								</BreadcrumbLink>
							)}
						</BreadcrumbItem>
						{!item.isLast && <BreadcrumbSeparator />}
					</React.Fragment>
				))}
			</BreadcrumbList>
		</Breadcrumb>
	);
}

function capitalizeFirstLetter(str: string): string {
	return str.charAt(0).toUpperCase() + str.slice(1).replace(/-/g, " ");
}
