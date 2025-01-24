<?php

namespace JoshuaJones\UserService\Http;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JoshuaJones\UserService\Contracts\HttpClientInterface;
use JoshuaJones\UserService\Exceptions\ApiException;

/**
 * Guzzle implementation of the HttpClientInterface for making HTTP requests.
 *
 * This class provides a wrapper around the Guzzle HTTP client library, implementing
 * the HttpClientInterface contract to standardize HTTP operations within the application.
 * It handles:
 * - Making HTTP GET and POST requests
 * - JSON response parsing
 * - Error handling and conversion to domain-specific exceptions
 *
 * Key features:
 * - Automatic JSON decoding of responses
 * - Consistent error handling through ApiException
 * - Support for Guzzle request options (query params, headers, etc)
 * - Type-safe response handling
 *
 * Usage example:
 * ```php
 * $client = new GuzzleHttpClient(new \GuzzleHttp\Client());
 * $response = $client->get('users/1'); // Returns decoded JSON as array
 * ```
 *
 * @package JoshuaJones\UserService\Http
 *
 * @see \JoshuaJones\UserService\Contracts\HttpClientInterface
 * @see \GuzzleHttp\ClientInterface
 * @see \JoshuaJones\UserService\Exceptions\ApiException
 *
 * @throws ApiException When HTTP requests fail or responses cannot be decoded
 */
class GuzzleHttpClient implements HttpClientInterface
{
    private GuzzleClientInterface $client;

    public function __construct(GuzzleClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Performs an HTTP GET request to the specified URI.
     *
     * @param string $uri     The URI to send the GET request to
     * @param array  $options Request options to apply to the request (e.g. query parameters, headers)
     *
     * @return array The decoded JSON response as an associative array
     *
     * @throws ApiException When the request fails or response cannot be decoded
     */
    public function get(string $uri, array $options = []): array
    {
        try {
            $response = $this->client->request('GET', $uri, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new ApiException('HTTP GET request failed', 0, ['uri' => $uri, 'options' => $options], $e);
        }
    }

    /**
     * Performs an HTTP POST request to the specified URI.
     *
     * @param string $uri     The URI to send the POST request to
     * @param array  $options Request options to apply to the request (e.g. request body, headers)
     *
     * @return array The decoded JSON response as an associative array
     *
     * @throws ApiException When the request fails or response cannot be decoded
     */
    public function post(string $uri, array $options = []): array
    {
        try {
            $response = $this->client->request('POST', $uri, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new ApiException('HTTP POST request failed', 0, ['uri' => $uri, 'options' => $options], $e);
        }
    }
}
