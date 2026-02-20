import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach } from "vitest";
import HomePage from "./index";

vi.mock("sonner", () => ({
	toast: {
		error: vi.fn(),
	},
}));

describe("HomePage", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("should render page title", () => {
		render(<HomePage />);
		expect(screen.getByText("Yuasa Battery Inspection")).toBeInTheDocument();
	});

	it("should display camera status indicator", () => {
		render(<HomePage />);
		expect(screen.getByText("Camera")).toBeInTheDocument();
		expect(screen.getByText("YOLO Detection")).toBeInTheDocument();
	});

	it("should show camera feed when status is active", () => {
		render(<HomePage />);
		const cameraFeed = screen.getByAltText("Camera feed");
		expect(cameraFeed).toBeInTheDocument();
		expect(cameraFeed).toHaveAttribute(
			"src",
			"http://localhost:8081/stream",
		);
	});

	it("should display 'No violations detected yet' when no violations", () => {
		render(<HomePage />);
		expect(
			screen.getByText("No violations detected yet"),
		).toBeInTheDocument();
	});

	it("should display status connection section", () => {
		render(<HomePage />);
		expect(screen.getByText("Status Connection")).toBeInTheDocument();
		expect(
			screen.getByText("Server connection status"),
		).toBeInTheDocument();
	});

	it("should display last detected section", () => {
		render(<HomePage />);
		expect(screen.getByText("Last Detected")).toBeInTheDocument();
		expect(
			screen.getByText("Most recent violation"),
		).toBeInTheDocument();
	});

	it("should display source type as CCTV", () => {
		render(<HomePage />);
		expect(screen.getByText("Source Type")).toBeInTheDocument();
		expect(screen.getByText("CCTV")).toBeInTheDocument();
	});
});
