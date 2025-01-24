<?php

namespace JoshuaJones\UserService\Contracts;

/**
 * Interface for HTTP client implementations.
 *
 * This interface defines the contract for HTTP client classes that handle
 * API requests. It provides methods for making GET and POST requests with
 * consistent return types and error handling.
 *
 * Implementations should:
 * - Handle JSON encoding/decoding of request/response data
 * - Convert HTTP errors into domain-specific exceptions
 * - Accept request options for flexibility (headers, query params, etc)
 * - Return responses as associative arrays
 *
 * @package JoshuaJones\UserService\Contracts
 *
 * @see \JoshuaJones\UserService\Http\GuzzleHttpClient For a concrete implementation
 * @see \JoshuaJones\UserService\Exceptions\ApiException For error handling
 */
interface HttpClientInterface
{
    public function get(string $url, array $options = []): array;
    public function post(string $url, array $options = []): array;
}
