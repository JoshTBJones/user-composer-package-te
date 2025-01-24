<?php

namespace JoshuaJones\UserService\Exceptions;

use Exception;
use Throwable;

/**
 * Custom exception class for handling API-related errors and exceptions.
 *
 * This exception is used throughout the application to handle various API-related
 * error scenarios including:
 * - Invalid JSON responses
 * - Rate limiting issues
 * - Invalid API responses
 * - HTTP client errors (4xx)
 * - Server errors (5xx)
 *
 * Key features:
 * - Stores additional context data about the error
 * - Provides helper methods for common API error scenarios
 * - Includes HTTP status code handling
 * - JSON serialization support
 *
 * Usage example:
 * ```php
 * throw new ApiException('API request failed', 500, ['endpoint' => '/users']);
 * // Or using named constructors:
 * throw ApiException::forInvalidJson($jsonString);
 * ```
 *
 * @package JoshuaJones\UserService\Exceptions
 * 
 * @method static self forInvalidResponse(string $response) Creates exception for invalid API responses
 * @method static self forInvalidJson(string $json) Creates exception for invalid JSON data
 * @method static self forRateLimitExceeded(int $retryAfter) Creates exception for rate limit violations
 * 
 * @see \Exception
 */
class ApiException extends Exception
{
    public const INVALID_JSON = 'Invalid JSON received: %s';
    public const RATE_LIMIT_EXCEEDED = 'Rate limit exceeded. Try again in %d seconds.';
    public const INVALID_RESPONSE = 'Invalid response received: %s';

    private array $context;

    public function __construct(string $message = "", int $code = 0, array $context = [], ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Creates an ApiException instance for invalid API responses.
     *
     * This named constructor creates an exception specifically for cases where
     * the API returns an invalid or unexpected response format. It automatically
     * sets the HTTP status code to 400 (Bad Request) and formats the error message
     * using the INVALID_RESPONSE constant template.
     *
     * @param string $response The invalid response content that triggered the error
     *
     * @return self A new ApiException instance configured for invalid response errors
     *
     * @example
     * // Creating exception for an invalid response
     * throw ApiException::forInvalidResponse($responseBody);
     *
     * @see ApiException::INVALID_RESPONSE For the message template used
     */
    public static function forInvalidResponse(string $response): self
    {
        return new self(sprintf(self::INVALID_RESPONSE, $response), 400);
    }

    /**
     * Creates an ApiException instance for invalid JSON data.
     *
     * This named constructor creates an exception specifically for cases where
     * JSON parsing fails or the JSON data is malformed. It automatically sets
     * the HTTP status code to 400 (Bad Request) and formats the error message
     * using the INVALID_JSON constant template.
     *
     * @param string $json The invalid JSON string that triggered the error
     *
     * @return self A new ApiException instance configured for invalid JSON errors
     *
     * @example
     * // Creating exception for malformed JSON
     * throw ApiException::forInvalidJson($jsonString);
     *
     * @see ApiException::INVALID_JSON For the message template used
     */
    public static function forInvalidJson(string $json): self
    {
        return new self(sprintf(self::INVALID_JSON, $json), 400);
    }

    /**
     * Creates an ApiException instance for rate limit exceeded errors.
     *
     * This named constructor creates an exception specifically for cases where
     * the API rate limit has been exceeded. It automatically sets the HTTP status
     * code to 429 (Too Many Requests) and formats the error message using the
     * RATE_LIMIT_EXCEEDED constant template.
     *
     * @param int $retryAfter The number of seconds to wait before retrying the request
     *
     * @return self A new ApiException instance configured for rate limit errors
     *
     * @example
     * // Creating exception when rate limit is exceeded
     * throw ApiException::forRateLimitExceeded(60); // Retry after 60 seconds
     *
     * @see ApiException::RATE_LIMIT_EXCEEDED For the message template used
     */
    public static function forRateLimitExceeded(int $retryAfter): self
    {
        return new self(sprintf(self::RATE_LIMIT_EXCEEDED, $retryAfter), 429);
    }

    /**
     * Returns the context array associated with this exception.
     * 
     * The context array contains additional information about the circumstances
     * that led to this exception being thrown. This can include details like:
     * - Request parameters
     * - URI information
     * - Request options
     * - Other relevant debugging data
     * 
     * This method provides access to this contextual data which can be useful
     * for debugging and logging purposes.
     *
     * @return array An associative array containing contextual information about the exception
     *
     * @example
     * // Accessing exception context
     * $exception = new ApiException("Error message", 400, ['uri' => '/api/users']);
     * $context = $exception->getContext(); // Returns ['uri' => '/api/users']
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Determines if the exception represents a client error (4xx HTTP status code).
     *
     * This method checks if the exception's status code falls within the 4xx range,
     * which indicates that the error was caused by the client's request. Client errors
     * typically include:
     * - 400 Bad Request
     * - 401 Unauthorized
     * - 403 Forbidden
     * - 404 Not Found
     * - 429 Too Many Requests
     *
     * @return bool True if the status code is between 400-499 (inclusive), false otherwise
     *
     * @example
     * // Checking if an exception represents a client error
     * $exception = new ApiException("Not Found", 404);
     * if ($exception->isClientError()) {
     *     // Handle client error case
     * }
     *
     * @see isServerError() For checking server errors (5xx)
     */
    public function isClientError(): bool
    {
        return $this->code >= 400 && $this->code < 500;
    }

    /**
     * Determines if the exception represents a server error (5xx HTTP status code).
     *
     * This method checks if the exception's status code falls within the 5xx range,
     * which indicates that the error occurred on the server side. Server errors
     * typically include:
     * - 500 Internal Server Error
     * - 501 Not Implemented
     * - 502 Bad Gateway
     * - 503 Service Unavailable
     * - 504 Gateway Timeout
     *
     * @return bool True if the status code is 500 or greater, false otherwise
     *
     * @example
     * // Checking if an exception represents a server error
     * $exception = new ApiException("Internal Server Error", 500);
     * if ($exception->isServerError()) {
     *     // Handle server error case
     * }
     *
     * @see isClientError() For checking client errors (4xx)
     */
    public function isServerError(): bool
    {
        return $this->code >= 500;
    }

    /**
     * Converts the exception details to a JSON string representation.
     *
     * This method serializes the exception's core properties (message, code, and context)
     * into a JSON format, making it suitable for:
     * - API error responses
     * - Logging purposes
     * - Client-side error handling
     *
     * The resulting JSON object contains:
     * - message: The exception message
     * - code: The HTTP status code
     * - context: Additional error context data
     *
     * @return string A JSON encoded string containing the exception details
     *
     * @example
     * // Converting exception to JSON
     * $exception = new ApiException("Not Found", 404, ['id' => 123]);
     * $jsonError = $exception->toJson();
     * // Results in: {"message":"Not Found","code":404,"context":{"id":123}}
     *
     * @see getMessage() For accessing the raw message
     * @see getCode() For accessing the raw status code
     * @see getContext() For accessing the raw context array
     */
    public function toJson(): string
    {
        return json_encode([
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'context' => $this->getContext(),
        ]);
    }
}
