import { createFileRoute } from "@tanstack/react-router";
import ViolationHistoryPage from "../../../features/ViolationHistoryPage";

export const Route = createFileRoute("/(dashboard)/violation-history/")({
  component: ViolationHistoryPage,
});
