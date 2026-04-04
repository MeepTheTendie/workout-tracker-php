const { test, expect } = require('@playwright/test');

const TEST_EMAIL = 'wesleympennock@gmail.com';
const TEST_PASSWORD = 'GrrMeep#5Dude';

test.describe('Navigation', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
  });

  test('should have bottom navigation on all pages', async ({ page }) => {
    const pages = ['/dashboard', '/workouts/log', '/workouts/history', '/routines', '/stats', '/prs', '/goals', '/cardio'];
    
    for (const path of pages) {
      await page.goto(path);
      await expect(page.locator('nav')).toBeVisible();
    }
  });

  test('should highlight active navigation item', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page.locator('nav a[href="/dashboard"]')).toHaveClass(/active/);
    
    await page.goto('/workouts/log');
    await expect(page.locator('nav a[href="/workouts/log"]')).toHaveClass(/active/);
    
    await page.goto('/routines');
    await expect(page.locator('nav a[href="/routines"]')).toHaveClass(/active/);
  });

  test('should navigate to all main sections', async ({ page }) => {
    // Dashboard
    await page.goto('/dashboard');
    await expect(page.locator('h1')).toContainText('Dashboard');
    
    // Log
    await page.goto('/workouts/log');
    await expect(page.locator('h1')).toBeVisible();
    
    // History
    await page.goto('/workouts/history');
    await expect(page.locator('h1')).toContainText('History');
    
    // Routines
    await page.goto('/routines');
    await expect(page.locator('h1')).toContainText('Routines');
    
    // Stats
    await page.goto('/stats');
    await expect(page.locator('h1')).toContainText('Stats');
    
    // PRs
    await page.goto('/prs');
    await expect(page.locator('h1')).toContainText('PRs');
    
    // Goals
    await page.goto('/goals');
    await expect(page.locator('h1')).toContainText('Goals');
    
    // Cardio
    await page.goto('/cardio');
    await expect(page.locator('h1')).toContainText('Cardio');
  });
});

test.describe('Security', () => {
  test('should redirect unauthenticated users to login', async ({ page }) => {
    const protectedPages = ['/dashboard', '/workouts/log', '/workouts/history', '/routines', '/stats'];
    
    for (const path of protectedPages) {
      await page.goto(path);
      await expect(page).toHaveURL('/login');
    }
  });

  test('should have CSRF tokens on forms', async ({ page }) => {
    await page.goto('/');
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
    
    // Check workout log form
    await page.goto('/workouts/log');
    await page.click('button:has-text("START NEW WORKOUT")');
    
    const csrfInput = page.locator('input[name="csrf_token"]');
    await expect(csrfInput).toHaveAttribute('type', 'hidden');
    const token = await csrfInput.inputValue();
    expect(token).toBeTruthy();
    expect(token.length).toBeGreaterThan(20);
  });

  test('should reject requests without CSRF token', async ({ page }) => {
    await page.goto('/');
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
    
    // Try to post without CSRF token
    const response = await page.request.post('/action/workouts/start', {
      form: {}
    });
    expect(response.status()).toBe(403);
  });
});
