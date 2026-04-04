const { test, expect } = require('@playwright/test');

const TEST_EMAIL = 'wesleympennock@gmail.com';
const TEST_PASSWORD = 'GrrMeep#5Dude';

test.describe('Workout Flow', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
    
    // Finish any active workout first
    await page.goto('/workouts/log');
    const finishButton = page.locator('button:has-text("FINISH WORKOUT")');
    if (await finishButton.isVisible().catch(() => false)) {
      await finishButton.click();
      await expect(page).toHaveURL('/dashboard');
    }
  });

  test('should start a new freestyle workout', async ({ page }) => {
    await page.goto('/workouts/log');
    
    await expect(page.locator('h1')).toContainText('Start Workout');
    await page.click('button:has-text("START NEW WORKOUT")');
    
    await expect(page).toHaveURL('/workouts/log');
    await expect(page.locator('.flash-success')).toContainText('Workout started!');
    await expect(page.locator('h1')).toContainText('Workout In Progress');
  });

  test('should not allow starting workout when one is active', async ({ page }) => {
    // Start first workout
    await page.goto('/workouts/log');
    await page.click('button:has-text("START NEW WORKOUT")');
    await expect(page.locator('h1')).toContainText('Workout In Progress');
    
    // Try to start another
    await page.goto('/workouts/log');
    await expect(page.locator('.flash-error')).toContainText('You already have an active workout');
  });

  test('should add an exercise to workout', async ({ page }) => {
    // Start workout
    await page.goto('/workouts/log');
    await page.click('button:has-text("START NEW WORKOUT")');
    
    // Add exercise
    await page.selectOption('select[name="exercise_id"]', { index: 1 });
    await page.fill('input[name="reps"]', '10');
    await page.fill('input[name="weight"]', '135');
    await page.click('button:has-text("ADD EXERCISE")');
    
    await expect(page.locator('.flash-success')).toContainText('Set added!');
    await expect(page.locator('.exercise-card')).toBeVisible();
  });

  test('should show progression hint for known exercises', async ({ page }) => {
    // Start workout
    await page.goto('/workouts/log');
    await page.click('button:has-text("START NEW WORKOUT")');
    
    // Select a known exercise with progression rule
    await page.selectOption('select[name="exercise_id"]', { label: /Leg Press/ });
    
    // Wait for progression hint
    await expect(page.locator('#progressionHint')).toBeVisible();
  });

  test('should validate exercise inputs', async ({ page }) => {
    // Start workout
    await page.goto('/workouts/log');
    await page.click('button:has-text("START NEW WORKOUT")');
    
    // Try to submit without selecting exercise
    const exerciseSelect = page.locator('select[name="exercise_id"]');
    await expect(exerciseSelect).toHaveAttribute('required', '');
    
    // Try invalid reps
    const repsInput = page.locator('input[name="reps"]');
    await expect(repsInput).toHaveAttribute('min', '1');
    
    // Try invalid weight
    const weightInput = page.locator('input[name="weight"]');
    await expect(weightInput).toHaveAttribute('min', '0');
  });

  test('should finish workout', async ({ page }) => {
    // Start workout
    await page.goto('/workouts/log');
    await page.click('button:has-text("START NEW WORKOUT")');
    
    // Add an exercise
    await page.selectOption('select[name="exercise_id"]', { index: 1 });
    await page.fill('input[name="reps"]', '10');
    await page.fill('input[name="weight"]', '135');
    await page.click('button:has-text("ADD EXERCISE")');
    
    // Finish workout
    await page.click('button:has-text("FINISH WORKOUT")');
    
    await expect(page).toHaveURL('/dashboard');
    await expect(page.locator('.flash-success')).toContainText('Workout completed');
  });

  test('should show active workout on dashboard', async ({ page }) => {
    // Start workout
    await page.goto('/workouts/log');
    await page.click('button:has-text("START NEW WORKOUT")');
    
    // Go to dashboard
    await page.goto('/dashboard');
    
    await expect(page.locator('.workout-in-progress-card')).toBeVisible();
    await expect(page.locator('.workout-in-progress-title')).toContainText('Workout In Progress');
  });
});

test.describe('Workout History', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await page.fill('input[name="email"]', TEST_EMAIL);
    await page.fill('input[name="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
  });

  test('should view workout history', async ({ page }) => {
    await page.goto('/workouts/history');
    
    await expect(page.locator('h1')).toContainText('History');
  });

  test('should view individual workout details', async ({ page }) => {
    // First create a workout
    await page.goto('/workouts/log');
    const startButton = page.locator('button:has-text("START NEW WORKOUT")');
    if (await startButton.isVisible().catch(() => false)) {
      await startButton.click();
      await page.selectOption('select[name="exercise_id"]', { index: 1 });
      await page.fill('input[name="reps"]', '10');
      await page.fill('input[name="weight"]', '135');
      await page.click('button:has-text("ADD EXERCISE")');
      await page.click('button:has-text("FINISH WORKOUT")');
    }
    
    // View history
    await page.goto('/workouts/history');
    
    // Click on first workout if available
    const workoutLink = page.locator('.workout-item a, a[href^="/workouts/view"]').first();
    if (await workoutLink.isVisible().catch(() => false)) {
      await workoutLink.click();
      await expect(page.locator('h1')).toContainText('Workout');
    }
  });
});
