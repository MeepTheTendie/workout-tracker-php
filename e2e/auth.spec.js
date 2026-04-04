const { test, expect } = require('@playwright/test');

const TEST_EMAIL = 'wesleympennock@gmail.com';
const TEST_PASSWORD = 'GrrMeep#5Dude';

test.describe('Authentication', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should show login page for unauthenticated users', async ({ page }) => {
    await expect(page.locator('h1')).toContainText('Workout Tracker');
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toContainText('LOGIN');
  });

  test('should login with valid credentials', async ({ page }) => {
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');
    
    // Should redirect to dashboard
    await expect(page).toHaveURL('/dashboard');
    await expect(page.locator('h1')).toContainText('Dashboard');
    await expect(page.locator('.welcome-banner')).toContainText('Welcome back!');
  });

  test('should show error for invalid credentials', async ({ page }) => {
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');
    
    // Should stay on login page with error
    await expect(page).toHaveURL('/login');
    await expect(page.locator('.flash-error')).toContainText('Invalid email or password');
  });

  test('should require email field', async ({ page }) => {
    await page.fill('input[name="password"]', TEST_PASSWORD);
    
    // HTML5 validation should prevent submission
    const emailInput = page.locator('input[name="email"]');
    await expect(emailInput).toHaveAttribute('required', '');
  });

  test('should require password field', async ({ page }) => {
    await page.fill('input[name="email"]', TEST_EMAIL);
    
    // HTML5 validation should prevent submission
    const passwordInput = page.locator('input[name="password"]');
    await expect(passwordInput).toHaveAttribute('required', '');
  });

  test('should redirect to dashboard when accessing login while authenticated', async ({ page }) => {
    // Login first
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
    
    // Try to access login page again
    await page.goto('/login');
    await expect(page).toHaveURL('/dashboard');
  });

  test('should logout successfully', async ({ page }) => {
    // Login first
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
    
    // Logout
    await page.click('button:has-text("Logout")');
    
    // Should redirect to login
    await expect(page).toHaveURL('/login');
    
    // Should not be able to access dashboard
    await page.goto('/dashboard');
    await expect(page).toHaveURL('/login');
  });
});

test.describe('Password Change', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
  });

  test('should navigate to change password page', async ({ page }) => {
    await page.click('a:has-text("Change Password")');
    await expect(page).toHaveURL('/change-password');
    await expect(page.locator('h1')).toContainText('Change Password');
  });

  test('should require all password fields', async ({ page }) => {
    await page.goto('/change-password');
    
    await expect(page.locator('input[name="current_password"]')).toHaveAttribute('required', '');
    await expect(page.locator('input[name="new_password"]')).toHaveAttribute('required', '');
    await expect(page.locator('input[name="confirm_password"]')).toHaveAttribute('required', '');
  });

  test('should require minimum 8 characters for new password', async ({ page }) => {
    await page.goto('/change-password');
    
    const newPasswordInput = page.locator('input[name="new_password"]');
    await expect(newPasswordInput).toHaveAttribute('minlength', '8');
  });

  test('should show error for incorrect current password', async ({ page }) => {
    await page.goto('/change-password');
    
    await page.fill('input[name="current_password"]', 'wrongpassword');
    await page.fill('input[name="new_password"]', 'NewPass123!');
    await page.fill('input[name="confirm_password"]', 'NewPass123!');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('.flash-error')).toContainText('Current password is incorrect');
  });

  test('should show error when passwords do not match', async ({ page }) => {
    await page.goto('/change-password');
    
    await page.fill('input[name="current_password"]', TEST_PASSWORD);
    await page.fill('input[name="new_password"]', 'NewPass123!');
    await page.fill('input[name="confirm_password"]', 'DifferentPass456!');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('.flash-error')).toContainText('New passwords do not match');
  });
});
