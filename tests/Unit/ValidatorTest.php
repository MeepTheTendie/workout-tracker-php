<?php
/**
 * Validator Unit Tests
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/validator.php';

class ValidatorTest extends TestCase
{
    public function testRequiredFieldPresent(): void
    {
        $data = ['name' => 'John'];
        $rules = ['name' => 'required'];
        
        $result = validate($data, $rules);
        
        $this->assertEquals(['name' => 'John'], $result);
    }
    
    public function testRequiredFieldMissing(): void
    {
        $data = [];
        $rules = ['name' => 'required'];
        
        expectValidationError(function() use ($data, $rules) {
            validate($data, $rules);
        }, 'Name is required');
    }
    
    public function testRequiredFieldEmpty(): void
    {
        $data = ['name' => ''];
        $rules = ['name' => 'required'];
        
        expectValidationError(function() use ($data, $rules) {
            validate($data, $rules);
        }, 'Name is required');
    }
    
    public function testIntegerValidation(): void
    {
        $data = ['age' => '25'];
        $rules = ['age' => 'int'];
        
        $result = validate($data, $rules);
        
        $this->assertSame(25, $result['age']);
    }
    
    public function testIntegerValidationInvalid(): void
    {
        $data = ['age' => 'not_a_number'];
        $rules = ['age' => 'int'];
        
        expectValidationError(function() use ($data, $rules) {
            validate($data, $rules);
        }, 'must be a whole number');
    }
    
    public function testFloatValidation(): void
    {
        $data = ['weight' => '135.5'];
        $rules = ['weight' => 'float'];
        
        $result = validate($data, $rules);
        
        $this->assertSame(135.5, $result['weight']);
    }
    
    public function testMinValidationInt(): void
    {
        $data = ['reps' => 5];
        $rules = ['reps' => 'int|min:1'];
        
        $result = validate($data, $rules);
        
        $this->assertSame(5, $result['reps']);
    }
    
    public function testMinValidationIntTooSmall(): void
    {
        $data = ['reps' => 0];
        $rules = ['reps' => 'int|min:1'];
        
        expectValidationError(function() use ($data, $rules) {
            validate($data, $rules);
        }, 'must be at least 1');
    }
    
    public function testMaxValidationInt(): void
    {
        $data = ['reps' => 100];
        $rules = ['reps' => 'int|max:999'];
        
        $result = validate($data, $rules);
        
        $this->assertSame(100, $result['reps']);
    }
    
    public function testMaxValidationIntTooLarge(): void
    {
        $data = ['reps' => 1000];
        $rules = ['reps' => 'int|max:999'];
        
        expectValidationError(function() use ($data, $rules) {
            validate($data, $rules);
        }, 'must be at most 999');
    }
    
    public function testEmailValidation(): void
    {
        $data = ['email' => 'Test@Email.COM'];
        $rules = ['email' => 'required|email'];
        
        $result = validate($data, $rules);
        
        $this->assertSame('test@email.com', $result['email']);
    }
    
    public function testEmailValidationInvalid(): void
    {
        $data = ['email' => 'not_an_email'];
        $rules = ['email' => 'email'];
        
        expectValidationError(function() use ($data, $rules) {
            validate($data, $rules);
        }, 'valid email address');
    }
    
    public function testPasswordValidation(): void
    {
        $data = ['password' => 'longpassword123'];
        $rules = ['password' => 'password'];
        
        $result = validate($data, $rules);
        
        $this->assertSame('longpassword123', $result['password']);
    }
    
    public function testPasswordValidationTooShort(): void
    {
        $data = ['password' => 'short'];
        $rules = ['password' => 'password'];
        
        expectValidationError(function() use ($data, $rules) {
            validate($data, $rules);
        }, 'at least 8 characters');
    }
    
    public function testStringTrimming(): void
    {
        $data = ['name' => '  John Doe  '];
        $rules = ['name' => 'string'];
        
        $result = validate($data, $rules);
        
        $this->assertSame('John Doe', $result['name']);
    }
    
    public function testNullableFieldWithValue(): void
    {
        $data = ['notes' => 'Some notes'];
        $rules = ['notes' => 'nullable|string'];
        
        $result = validate($data, $rules);
        
        $this->assertSame('Some notes', $result['notes']);
    }
    
    public function testNullableFieldWithNull(): void
    {
        $data = ['notes' => ''];
        $rules = ['notes' => 'nullable|string'];
        
        $result = validate($data, $rules);
        
        $this->assertNull($result['notes']);
    }
    
    public function testInValidation(): void
    {
        $data = ['status' => 'active'];
        $rules = ['status' => 'in:active,inactive,pending'];
        
        $result = validate($data, $rules);
        
        $this->assertSame('active', $result['status']);
    }
    
    public function testInValidationInvalid(): void
    {
        $data = ['status' => 'deleted'];
        $rules = ['status' => 'in:active,inactive,pending'];
        
        expectValidationError(function() use ($data, $rules) {
            validate($data, $rules);
        }, 'is invalid');
    }
    
    public function testComplexValidation(): void
    {
        $data = [
            'exercise_id' => '5',
            'reps' => '10',
            'weight' => '135.5',
            'notes' => ''
        ];
        $rules = [
            'exercise_id' => 'required|int|min:1',
            'reps' => 'required|int|min:1|max:999',
            'weight' => 'required|float|min:0|max:9999',
            'notes' => 'nullable|string'
        ];
        
        $result = validate($data, $rules);
        
        $this->assertSame(5, $result['exercise_id']);
        $this->assertSame(10, $result['reps']);
        $this->assertSame(135.5, $result['weight']);
        $this->assertNull($result['notes']);
    }
    
    public function testValidateValueHelper(): void
    {
        $result = validateValue('25', 'int|min:1|max:100');
        
        $this->assertSame(25, $result);
    }
}
