import { createRootRouteWithContext, Outlet } from "@tanstack/react-router";
import { ThemeProvider } from "../components/theme-provider";
import { Toaster } from "../components/ui/sonner";
import Configuration from "../config";
import { useAuthStore } from "../store/use-auth";

export interface RouterContext {
  authentication: ReturnType<typeof useAuthStore>;
}

document.title = Configuration.appName;

export const Route = createRootRouteWithContext<RouterContext>()({
  component: () => (
    <ThemeProvider defaultTheme="light">
      <Outlet />
      <Toaster />
    </ThemeProvider>
  ),
});
