<?php
/**
 * Progression Rules Unit Tests
 * 
 * Tests the progression rules system including:
 * - getProgressionRules()
 * - suggestNextWeight() (from helpers.php)
 * - progressionNote() (from helpers.php)
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/progression.php';
require_once __DIR__ . '/../../includes/helpers.php';

class ProgressionTest extends TestCase
{
    public function testGetProgressionRulesReturnsArray(): void
    {
        $rules = getProgressionRules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
    }
    
    public function testKnownExerciseHasRule(): void
    {
        $rule = getProgressionRule('Back Extension');
        
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('increment', $rule);
        $this->assertArrayHasKey('threshold', $rule);
        $this->assertArrayHasKey('note', $rule);
    }
    
    public function testUnknownExerciseReturnsNull(): void
    {
        $rule = getProgressionRule('Unknown Exercise');
        
        $this->assertNull($rule);
    }
    
    public function testSuggestNextWeightWithSimpleIncrement(): void
    {
        $result = suggestNextWeight('Back Extension', 100);
        
        // Back Extension has increment of 15
        $this->assertSame(115.0, $result);
    }
    
    public function testSuggestNextWeightWithThreshold(): void
    {
        // Low Back - Roc It: +15 normally, +20 after 45 lbs
        $result = suggestNextWeight('Low Back - Roc It', 30);
        
        $this->assertSame(45.0, $result); // 30 + 15
    }
    
    public function testSuggestNextWeightAfterThreshold(): void
    {
        // Low Back - Roc It: +15 normally, +20 after 100 lbs
        $result = suggestNextWeight('Low Back - Roc It', 105);
        
        $this->assertSame(125.0, $result); // 105 + 20
    }
    
    public function testSuggestNextWeightAtThreshold(): void
    {
        // Low Back - Roc It: +15 normally, +20 after 100 lbs
        $result = suggestNextWeight('Low Back - Roc It', 100);
        
        $this->assertSame(120.0, $result); // 100 + 20 (>= threshold)
    }
    
    public function testSuggestNextWeightNoRule(): void
    {
        $result = suggestNextWeight('Unknown Exercise', 100);
        
        // Returns last weight when no rule exists
        $this->assertSame(100.0, $result);
    }
    
    public function testSuggestNextWeightNullLastWeight(): void
    {
        $result = suggestNextWeight('Back Extension', null);
        
        $this->assertNull($result);
    }
    
    public function testProgressionNoteKnownExercise(): void
    {
        $note = progressionNote('Leg Press');
        
        $this->assertSame('+15 lbs', $note);
    }
    
    public function testProgressionNoteUnknownExercise(): void
    {
        $note = progressionNote('Unknown Exercise');
        
        $this->assertSame('', $note);
    }
    
    public function testProgressionNoteWithThreshold(): void
    {
        $note = progressionNote('Low Back - Roc It');
        
        $this->assertSame('+15 lbs (then +20 after 100)', $note);
    }
    
    public function testGetProgressionRulesJson(): void
    {
        $json = getProgressionRulesJson();
        
        $this->assertJson($json);
        
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('Back Extension', $decoded);
        $this->assertArrayHasKey('inc', $decoded['Back Extension']);
    }
    
    public function testAllRulesHaveRequiredFields(): void
    {
        $rules = getProgressionRules();
        
        foreach ($rules as $name => $rule) {
            $this->assertArrayHasKey('increment', $rule, "Rule for $name missing increment");
            $this->assertArrayHasKey('threshold', $rule, "Rule for $name missing threshold");
            $this->assertArrayHasKey('after_threshold', $rule, "Rule for $name missing after_threshold");
            $this->assertArrayHasKey('note', $rule, "Rule for $name missing note");
            
            $this->assertIsInt($rule['increment'], "Rule for $name increment should be int");
            $this->assertIsString($rule['note'], "Rule for $name note should be string");
        }
    }
    
    public function testSpecificExercisesHaveRules(): void
    {
        $expectedExercises = [
            'Back Extension',
            'Low Back - Roc It',
            'Diverging Seated Row',
            'Leg Press',
            'Converging Chest Press',
            'Tricep Extensions',
            'Bicep Curl',
            'Shoulder Press - Machine',
        ];
        
        foreach ($expectedExercises as $exercise) {
            $rule = getProgressionRule($exercise);
            $this->assertNotNull($rule, "Exercise $exercise should have a progression rule");
        }
    }
}
