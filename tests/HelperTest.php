<?php
/**
 * Unit Tests for Helper Functions
 * 
 * @package WorkoutTracker
 * @subpackage Tests
 */

use PHPUnit\Framework\TestCase;

// Load the required files
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/helpers.php';

/**
 * Test case for helper functions
 */
class HelperTest extends TestCase
{
    /**
     * Test formatDate function with various inputs
     * 
     * @covers formatDate
     */
    public function testFormatDate(): void
    {
        // Test with seconds timestamp
        $timestamp = strtotime('2024-03-15');
        $this->assertEquals('Mar 15, 2024', formatDate($timestamp));
        
        // Test with milliseconds timestamp
        $msTimestamp = $timestamp * 1000;
        $this->assertEquals('Mar 15, 2024', formatDate($msTimestamp));
        
        // Test with null
        $this->assertEquals('N/A', formatDate(null));
        
        // Test with custom format
        $this->assertEquals('15-03-2024', formatDate($timestamp, 'd-m-Y'));
    }
    
    /**
     * Test timeAgo function returns correct relative time
     * 
     * @covers timeAgo
     */
    public function testTimeAgo(): void
    {
        // Test just now
        $this->assertEquals('just now', timeAgo(time() - 30));
        
        // Test minutes ago
        $this->assertEquals('5m ago', timeAgo(time() - 300));
        
        // Test hours ago
        $this->assertEquals('2h ago', timeAgo(time() - 7200));
        
        // Test days ago
        $this->assertEquals('3d ago', timeAgo(time() - 259200));
        
        // Test with null
        $this->assertEquals('never', timeAgo(null));
    }
    
    /**
     * Test formatWeight function formats weight correctly
     * 
     * @covers formatWeight
     */
    public function testFormatWeight(): void
    {
        $this->assertEquals('100.0 lbs', formatWeight(100));
        $this->assertEquals('100.5 lbs', formatWeight(100.5));
        $this->assertEquals('-', formatWeight(null));
    }
    
    /**
     * Test formatVolume function calculates volume correctly
     * 
     * @covers formatVolume
     */
    public function testFormatVolume(): void
    {
        $this->assertEquals('1,000 lbs', formatVolume(100, 10));
        $this->assertEquals('2,250 lbs', formatVolume(150, 15));
    }
    
    /**
     * Test suggestNextWeight function returns correct progression
     * 
     * @covers suggestNextWeight
     */
    public function testSuggestNextWeight(): void
    {
        // Test Back Extension (default +15)
        $this->assertEquals(115, suggestNextWeight('Back Extension', 100));
        
        // Test Leg Press (default +15)
        $this->assertEquals(215, suggestNextWeight('Leg Press', 200));
        
        // Test Low Back - Roc It with threshold (+15 under 100, +20 at/after 100)
        $this->assertEquals(55, suggestNextWeight('Low Back - Roc It', 40)); // 40 + 15 = 55
        $this->assertEquals(120, suggestNextWeight('Low Back - Roc It', 100)); // 100 + 20 = 120
        
        // Test unknown exercise returns same weight
        $this->assertEquals(100, suggestNextWeight('Unknown Exercise', 100));
        
        // Test null last weight - returns null when no rule exists
        $this->assertNull(suggestNextWeight('Unknown Exercise', null));
        // Test null with known exercise - also returns null
        $this->assertNull(suggestNextWeight('Back Extension', null));
    }
    
    /**
     * Test progressionNote function returns correct notes
     * 
     * @covers progressionNote
     */
    public function testProgressionNote(): void
    {
        $this->assertEquals('+15 lbs', progressionNote('Back Extension'));
        $this->assertEquals('+15 lbs (then +20 after 100)', progressionNote('Low Back - Roc It'));
        $this->assertEquals('', progressionNote('Unknown Exercise'));
    }
    
    /**
     * Test e() function escapes HTML entities
     * 
     * @covers e
     */
    public function testEscape(): void
    {
        $this->assertEquals('&lt;script&gt;', e('<script>'));
        $this->assertEquals('&quot;test&quot;', e('"test"'));
        $this->assertEquals('', e(''));
    }
}
