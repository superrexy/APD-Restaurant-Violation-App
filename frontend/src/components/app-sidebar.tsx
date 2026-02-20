import type * as React from "react";

import { NavMain } from "@/components/nav-main";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
} from "@/components/ui/sidebar";
import { Link } from "@tanstack/react-router";
import Configuration from "../config";
import { sidebar } from "../config/sidebar";
import { NavUser } from "./nav-user";

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
  return (
    <Sidebar collapsible="offcanvas" {...props}>
      <SidebarHeader>
        <div className="relative flex w-full min-w-0 flex-col p-2">
          <Link to="/" className="flex flex-col">
            <span className="text-2xl font-bold">{Configuration.appName}</span>
            <span className="text-sm">{Configuration.appDescription}</span>
          </Link>
        </div>
      </SidebarHeader>
      <SidebarContent>
        <NavMain items={sidebar.navMain} />
      </SidebarContent>
      <SidebarFooter>
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
