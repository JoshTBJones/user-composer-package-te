<?php

namespace JoshuaJones\UserService\Utils;

use InvalidArgumentException;
use JoshuaJones\UserService\Exceptions\ApiException;

/**
 * Utility class providing static validation methods for common data types.
 *
 * This class contains a collection of static validation methods that perform common
 * validation tasks throughout the application. It helps ensure data consistency and
 * provides clear error messages when validation fails. The class handles:
 * - Positive integer validation (e.g., IDs, counts)
 * - Non-empty string validation (e.g., names, descriptions)
 * 
 * Key features:
 * - Static methods for easy access
 * - Consistent error messaging
 * - Type-specific validation rules
 * - Throws standard InvalidArgumentException
 *
 * Usage example:
 * ```php
 * // Validate a user ID
 * Validators::validatePositiveInteger($userId, 'User ID');
 * 
 * // Validate a username
 * Validators::validateNonEmptyString($username, 'Username');
 * ```
 *
 * @package JoshuaJones\UserService\Utils
 * 
 * @see InvalidArgumentException For the type of exception thrown on validation failures
 */
class Validators
{
    /**
     * Validates that a given integer value is positive (greater than 0).
     *
     * This method checks if the provided integer value is greater than 0 and throws
     * an exception if it is not. Used for validating parameters like IDs and page numbers
     * that must be positive integers.
     *
     * @param int    $value     The integer value to validate
     * @param string $fieldName The name of the field being validated (used in error message)
     *
     * @return void
     *
     * @throws InvalidArgumentException When the value is less than 1
     *
     * @example
     * ```php
     * // Validates a user ID
     * Validators::validatePositiveInteger($userId, 'User ID');
     * ```
     */
    public static function validatePositiveInteger(int $value, string $fieldName): void
    {
        if ($value < 1) {
            throw new InvalidArgumentException("{$fieldName} must be greater than 0.");
        }
    }

    /**
     * Validates that a given string value is not empty or only whitespace.
     *
     * This method checks if the provided string value contains any non-whitespace
     * characters. It trims the string before checking to ensure strings containing
     * only spaces are considered empty. Used for validating required string fields
     * like names, job titles, etc.
     *
     * @param string $value     The string value to validate
     * @param string $fieldName The name of the field being validated (used in error message)
     *
     * @return void
     *
     * @throws InvalidArgumentException When the string is empty or contains only whitespace
     *
     * @example
     * ```php
     * // Validates a user's name
     * Validators::validateNonEmptyString($name, 'Name');
     * ```
     */
    public static function validateNonEmptyString(string $value, string $fieldName): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException("{$fieldName} must not be empty.");
        }
    }

    /**
     * Validates that an API response array contains all required keys.
     *
     * This method checks if the provided API response array contains all the specified
     * required keys. It compares the keys present in the response against the list of
     * required keys and throws an exception if any required keys are missing.
     *
     * @param array $response     The API response array to validate
     * @param array $requiredKeys Array of keys that must exist in the response
     *
     * @return void
     *
     * @throws ApiException When one or more required keys are missing from the response
     *
     * @example
     * ```php
     * // Validates that a response has 'data' and 'meta' keys
     * Validators::validateApiResponseKeys($response, ['data', 'meta']);
     * ```
     */
    public static function validateApiResponseKeys(array $response, array $requiredKeys): void
    {
        $missingKeys = array_diff($requiredKeys, array_keys($response));
        if (!empty($missingKeys)) {
            throw ApiException::forInvalidResponse('Missing required keys: ' . implode(', ', $missingKeys));
        }
    }
}
