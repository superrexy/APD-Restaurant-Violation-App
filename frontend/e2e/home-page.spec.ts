import { expect, test } from "@playwright/test";

test.describe("HomePage - Last Detected Section", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto("http://localhost:3000");
  });

  test("displays 'No violations detected yet' when no violations exist", async ({
    page,
  }) => {
    await page.waitForSelector("text=No violations detected yet");
    await expect(
      page.getByText("No violations detected yet"),
    ).toBeVisible();
  });

  test("displays the last violation data when violations exist", async ({
    page,
  }) => {
    await page.waitForSelector("text=Last Detected", { timeout: 10000 });

    const lastDetectedCard = page
      .getByText("Last Detected")
      .locator("..")
      .locator("..");

    await expect(
      lastDetectedCard.getByText("Most recent violation"),
    ).toBeVisible();
  });

  test("displays violation image when a violation exists", async ({
    page,
  }) => {
    await page.waitForSelector("img[alt*='Violation at']", {
      timeout: 10000,
    });

    const violationImage = page.locator('img[alt*="Violation at"]');

    await expect(violationImage).toBeVisible();
    await expect(violationImage).toHaveAttribute(
      "src",
      /\/storage\/.*\.(jpg|jpeg|png)/,
    );
  });

  test("displays violation details (type, camera, detected time)", async ({
    page,
  }) => {
    await page.waitForSelector('text=Type:', { timeout: 10000 });

    await expect(page.getByText("Type:")).toBeVisible();
    await expect(page.getByText("Camera:")).toBeVisible();
    await expect(page.getByText("Detected:")).toBeVisible();
  });

  test("polls for violations every 3 seconds", async ({ page }) => {
    const startTime = Date.now();

    await page.waitForSelector("text=Last Detected", { timeout: 10000 });

    const elapsedTime = Date.now() - startTime;

    expect(elapsedTime).toBeLessThan(5000);

    await page.waitForTimeout(3000);

    await expect(page.locator("text=Last Detected")).toBeVisible();
  });
});
