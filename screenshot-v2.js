const { chromium } = require('playwright-core');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://localhost:8081';
const PASSWORD = 'test123';
const OUTPUT_DIR = path.join(__dirname, 'screenshots-for-nano-banana');

// Ensure output directory exists
if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

async function takeScreenshots() {
  console.log('Starting browser...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1280, height: 900 }
  });
  const page = await context.newPage();

  try {
    // 1. Screenshot Login Page
    console.log('📸 Capturing: Login Page');
    await page.goto(`${BASE_URL}/login`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ 
      path: path.join(OUTPUT_DIR, '01-login.png'),
      fullPage: true 
    });

    // 2. Login to access other pages
    console.log('🔐 Logging in...');
    await page.fill('input[name="password"]', PASSWORD);
    
    // Submit and wait for navigation
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.click('button[type="submit"]')
    ]);
    
    console.log('📸 Capturing: Dashboard');
    await page.screenshot({ 
      path: path.join(OUTPUT_DIR, '02-dashboard.png'),
      fullPage: true 
    });

    // 4. Screenshot Workouts History
    console.log('📸 Capturing: Workouts History');
    await page.goto(`${BASE_URL}/workouts/history`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ 
      path: path.join(OUTPUT_DIR, '03-workouts-history.png'),
      fullPage: true 
    });

    // 5. Screenshot Workout Log (active workout page)
    console.log('📸 Capturing: Workout Log Page');
    await page.goto(`${BASE_URL}/workouts/log`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ 
      path: path.join(OUTPUT_DIR, '04-workout-log.png'),
      fullPage: true 
    });

    // 6. Screenshot Stats
    console.log('📸 Capturing: Stats');
    await page.goto(`${BASE_URL}/stats`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ 
      path: path.join(OUTPUT_DIR, '05-stats.png'),
      fullPage: true 
    });

    // 7. Screenshot PRs
    console.log('📸 Capturing: PRs (Personal Records)');
    await page.goto(`${BASE_URL}/prs`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ 
      path: path.join(OUTPUT_DIR, '06-prs.png'),
      fullPage: true 
    });

    // 8. Screenshot Goals
    console.log('📸 Capturing: Goals');
    await page.goto(`${BASE_URL}/goals`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ 
      path: path.join(OUTPUT_DIR, '07-goals.png'),
      fullPage: true 
    });

    // 9. Screenshot Routines List
    console.log('📸 Capturing: Routines List');
    await page.goto(`${BASE_URL}/routines`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ 
      path: path.join(OUTPUT_DIR, '08-routines-list.png'),
      fullPage: true 
    });

    // 10. Screenshot Create Routine
    console.log('📸 Capturing: Create Routine');
    await page.goto(`${BASE_URL}/routines/create`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ 
      path: path.join(OUTPUT_DIR, '09-routines-create.png'),
      fullPage: true 
    });

    console.log('\n✅ All screenshots captured successfully!');
    console.log(`📁 Location: ${OUTPUT_DIR}`);
    
    // List all screenshots
    const files = fs.readdirSync(OUTPUT_DIR).sort();
    console.log('\n📋 Screenshots captured:');
    files.forEach(f => console.log(`   - ${f}`));

  } catch (error) {
    console.error('❌ Error:', error.message);
    process.exit(1);
  } finally {
    await browser.close();
  }
}

takeScreenshots();
