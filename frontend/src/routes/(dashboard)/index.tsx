import { createFileRoute } from "@tanstack/react-router";
import HomePage from "../../features/HomePage";

export const Route = createFileRoute("/(dashboard)/")({
  component: App,
});

function App() {
  return <HomePage />;
}
