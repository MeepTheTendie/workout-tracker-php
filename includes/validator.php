<?php
/**
 * Input Validation Layer
 * Provides declarative validation for form inputs
 */

class ValidationError extends Exception {}

/**
 * Validate input data against rules
 * 
 * @param array $data Input data (usually $_POST)
 * @param array $rules Validation rules: field => 'rule1|rule2:arg|...'
 * @return array Validated and sanitized data
 * @throws ValidationError on failure
 */
function validate(array $data, array $rules): array
{
    $validated = [];
    $errors = [];
    
    foreach ($rules as $field => $ruleString) {
        $value = $data[$field] ?? null;
        $fieldRules = explode('|', $ruleString);
        
        foreach ($fieldRules as $rule) {
            $ruleParts = explode(':', $rule, 2);
            $ruleName = $ruleParts[0];
            $ruleArg = $ruleParts[1] ?? null;
            
            try {
                $value = applyRule($field, $value, $ruleName, $ruleArg);
            } catch (ValidationError $e) {
                $errors[$field] = $e->getMessage();
                break;
            }
        }
        
        if (!isset($errors[$field])) {
            $validated[$field] = $value;
        }
    }
    
    if (!empty($errors)) {
        $firstError = reset($errors);
        throw new ValidationError($firstError);
    }
    
    return $validated;
}

/**
 * Apply a single validation rule
 * 
 * @param string $field Field name (for error messages)
 * @param mixed $value Input value
 * @param string $rule Rule name
 * @param string|null $arg Rule argument
 * @return mixed Sanitized value
 * @throws ValidationError
 */
function applyRule(string $field, $value, string $rule, ?string $arg)
{
    switch ($rule) {
        case 'required':
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                throw new ValidationError(ucfirst($field) . ' is required');
            }
            return $value;
            
        case 'int':
            $int = filter_var($value, FILTER_VALIDATE_INT);
            if ($int === false) {
                throw new ValidationError(ucfirst($field) . ' must be a whole number');
            }
            return $int;
            
        case 'float':
            $float = filter_var($value, FILTER_VALIDATE_FLOAT);
            if ($float === false) {
                throw new ValidationError(ucfirst($field) . ' must be a number');
            }
            return $float;
            
        case 'min':
            if ($arg === null) {
                return $value;
            }
            if (is_int($value) && $value < (int)$arg) {
                throw new ValidationError(ucfirst($field) . " must be at least {$arg}");
            }
            if (is_float($value) && $value < (float)$arg) {
                throw new ValidationError(ucfirst($field) . " must be at least {$arg}");
            }
            if (is_string($value) && strlen($value) < (int)$arg) {
                throw new ValidationError(ucfirst($field) . " must be at least {$arg} characters");
            }
            return $value;
            
        case 'max':
            if ($arg === null) {
                return $value;
            }
            if (is_int($value) && $value > (int)$arg) {
                throw new ValidationError(ucfirst($field) . " must be at most {$arg}");
            }
            if (is_float($value) && $value > (float)$arg) {
                throw new ValidationError(ucfirst($field) . " must be at most {$arg}");
            }
            if (is_string($value) && strlen($value) > (int)$arg) {
                throw new ValidationError(ucfirst($field) . " must be at most {$arg} characters");
            }
            return $value;
            
        case 'string':
            if (!is_string($value) && $value !== null) {
                throw new ValidationError(ucfirst($field) . ' must be text');
            }
            if ($value === null) {
                return null;  // null passes through string validation
            }
            return trim($value ?? '');
            
        case 'email':
            $email = filter_var($value, FILTER_VALIDATE_EMAIL);
            if ($email === false) {
                throw new ValidationError('Please enter a valid email address');
            }
            return strtolower($email);
            
        case 'password':
            if (strlen($value) < 8) {
                throw new ValidationError('Password must be at least 8 characters');
            }
            return $value;
            
        case 'in':
            if ($arg === null) {
                return $value;
            }
            $allowed = explode(',', $arg);
            if (!in_array($value, $allowed, true)) {
                throw new ValidationError(ucfirst($field) . ' is invalid');
            }
            return $value;
            
        case 'nullable':
            if ($value === null || $value === '') {
                return null;
            }
            return $value;
            
        case 'bool':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            
        default:
            return $value;
    }
}

/**
 * Quick validation helper for single values
 * 
 * @param mixed $value
 * @param string $rules
 * @return mixed
 * @throws ValidationError
 */
function validateValue($value, string $rules)
{
    $result = validate(['value' => $value], ['value' => $rules]);
    return $result['value'];
}
