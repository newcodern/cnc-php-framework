<?php
namespace Contingent;
class D_AyaNe
{
    protected static $data;
    protected static $rules;
    protected static $errors;
    protected static $validated;

    public static function validate($data, $rules)
    {
        self::$data = $data;
        self::$rules = $rules;
        self::$errors = [];
        self::$validated = [];

        foreach (self::$rules as $field => $fieldRules) {
            $rules = explode('|', $fieldRules);

            foreach ($rules as $rule) {
                self::applyRule($field, $rule);
            }
        }

        return self::$errors;
    }


    public static function validated($field)
    {
        return isset(self::$validated[$field]) ? self::$validated[$field] : null;
    }

    protected static function applyRule($field, $rule)
    {
        $params = explode(':', $rule);
        $ruleName = array_shift($params);

        $methodName = 'validate' . ucfirst($ruleName);

        if (method_exists(self::class, $methodName)) {
            call_user_func_array([self::class, $methodName], [$field, ...$params]);
        }
    }

    protected static function addError($field, $message)
    {
        self::$errors[$field][] = $message;
    }

protected static function validateRequired($field)
{
    if (
        !isset(self::$data[$field]) ||
        (is_string(self::$data[$field]) && trim(self::$data[$field]) === '') ||
        (is_array(self::$data[$field]) && empty(array_filter(self::$data[$field], 'trim')))
    ) {
        self::addError($field, 'The ' . $field . ' field is required and cannot be empty.');
    } else {
        self::$validated[$field] = self::$data[$field];
    }
}

    protected static function validateUnique($field, $parameter)
    {
        // Implement unique validation logic here
        // For example, you could check if the value is unique in the database
        // and add an error if it's not unique
    }

    protected static function validateMax($field, $parameter)
    {
        if (isset(self::$data[$field]) && strlen(self::$data[$field]) > $parameter) {
            self::addError($field, 'The ' . $field . ' field must not be greater than ' . $parameter . ' characters.');
        } else {
            self::$validated[$field] = self::$data[$field];
        }
    }
    protected static function validateUnderscore($field)
{
    if (isset(self::$data[$field]) && preg_match('/^[a-zA-Z0-9_]+$/', self::$data[$field]) !== 1) {
        self::addError($field, 'The ' . $field . ' field must only contain letters, numbers, and underscores.');
    } else {
        self::$validated[$field] = self::$data[$field];
    }
}

protected static function validateNoSpaces($field)
{
    if (isset(self::$data[$field]) && strpos(self::$data[$field], ' ') !== false) {
        self::addError($field, 'The ' . $field . ' field must not contain any spaces.');
    } else {
        self::$validated[$field] = self::$data[$field];
    }
}

}
?>