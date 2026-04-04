const { test, expect } = require('@playwright/test');

const TEST_EMAIL = 'wesleympennock@gmail.com';
const TEST_PASSWORD = 'GrrMeep#5Dude';

test.describe('Routines', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
  });

  test('should view routines list', async ({ page }) => {
    await page.goto('/routines');
    
    await expect(page.locator('h1')).toContainText('Routines');
  });

  test('should create a new routine', async ({ page }) => {
    await page.goto('/routines');
    
    await page.click('a:has-text("Create Routine")');
    await expect(page).toHaveURL('/routines/create');
    
    await page.fill('input[name="name"]', 'Test Routine ' + Date.now());
    await page.fill('textarea[name="description"]', 'Test routine description');
    await page.click('button[type="submit"]');
    
    await expect(page).toHaveURL('/routines');
    await expect(page.locator('.flash-success')).toContainText('Routine created');
  });

  test('should require routine name', async ({ page }) => {
    await page.goto('/routines/create');
    
    const nameInput = page.locator('input[name="name"]');
    await expect(nameInput).toHaveAttribute('required', '');
  });

  test('should start workout from routine', async ({ page }) => {
    await page.goto('/routines');
    
    // Find first routine with start button
    const startButton = page.locator('button:has-text("Start"), a:has-text("Start")').first();
    
    if (await startButton.isVisible().catch(() => false)) {
      // Finish any active workout first
      await page.goto('/workouts/log');
      const finishButton = page.locator('button:has-text("FINISH WORKOUT")');
      if (await finishButton.isVisible().catch(() => false)) {
        await finishButton.click();
        await expect(page).toHaveURL('/dashboard');
      }
      
      await page.goto('/routines');
      await startButton.click();
      
      await expect(page).toHaveURL('/workouts/log');
      await expect(page.locator('h1')).toContainText('Workout In Progress');
    }
  });
});
