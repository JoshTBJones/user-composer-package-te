<?php

namespace JoshuaJones\UserService\Services;

use JoshuaJones\UserService\Contracts\HttpClientInterface;
use JoshuaJones\UserService\Dto\User;
use JoshuaJones\UserService\Exceptions\ApiException;
use JoshuaJones\UserService\Http\GuzzleHttpClient;
use JoshuaJones\UserService\Utils\Validators;
use GuzzleHttp\Client;
use InvalidArgumentException;

/**
 * Service class for interacting with a User API.
 *
 * This service provides methods to interact with a RESTful User API, handling operations such as:
 * - Retrieving individual users by ID
 * - Fetching paginated lists of users
 * - Creating new user records
 *
 * The class uses dependency injection to accept an HttpClientInterface implementation,
 * defaulting to GuzzleHttpClient if none is provided. This allows for flexible HTTP client
 * usage and easier testing through mocking.
 *
 * All methods perform input validation and handle errors consistently by:
 * - Validating input parameters before making API calls
 * - Converting HTTP errors into domain-specific exceptions
 * - Ensuring response data meets expected formats
 * - Transforming raw API responses into strongly-typed DTOs
 *
 * @package JoshuaJones\UserService\Services
 *
 * @see \JoshuaJones\UserService\Contracts\HttpClientInterface
 * @see \JoshuaJones\UserService\Http\GuzzleHttpClient
 * @see \JoshuaJones\UserService\Dto\User
 * @see \JoshuaJones\UserService\Exceptions\ApiException
 *
 * @throws \InvalidArgumentException When input validation fails
 * @throws \JoshuaJones\UserService\Exceptions\ApiException When API operations fail
 */
class UserService
{
    private HttpClientInterface $client;

    public function __construct(?HttpClientInterface $client = null)
    {
        $this->client = $client ?? new GuzzleHttpClient(new Client());
    }

    /**
     * Retrieves a user by their ID from the API.
     *
     * This method fetches a single user's data from the API using their unique identifier.
     * It validates the input ID, makes an HTTP GET request, and processes the JSON response
     * to create a User object.
     *
     * @param int $id The unique identifier of the user to retrieve (must be greater than 0)
     *
     * @return User A User object containing the retrieved user's information
     *
     * @throws InvalidArgumentException When the provided ID is less than 1
     * @throws ApiException When:
     *                     - The API request fails
     *                     - The response contains invalid JSON
     *                     - The response format is unexpected (missing 'data' key)
     *                     - Any other API-related error occurs
     */
    public function getUserById(int $id): User
    {
        Validators::validatePositiveInteger($id, 'ID');

        try {
            // Get the JSON response from the client
            $response = $this->client->get("users/{$id}");

            // Validate the response keys
            Validators::validateApiResponseKeys($response, ['data']);

            // Extract the 'data' array and create a User object
            return User::fromArray($response['data']);
        } catch (\Exception $e) {
            // Re-throw the exception with a custom message
            throw new ApiException('Failed to fetch user', 0, ['id' => $id], $e);
        }
    }

    /**
     * Retrieves a paginated list of users from the API.
     *
     * This method fetches a list of users with pagination support. It validates the page number,
     * makes an HTTP GET request with the page parameter, and processes the JSON response to create
     * an array of User objects along with pagination metadata.
     *
     * @param int $page The page number to retrieve (must be greater than 0, defaults to 1)
     *
     * @return array An associative array containing:
     *               - users (User[]): Array of User objects for the current page
     *               - total (int): Total number of users across all pages
     *               - per_page (int): Number of users per page
     *               - page (int): Current page number
     *               - total_pages (int): Total number of available pages
     *
     * @throws InvalidArgumentException When the provided page number is less than 1
     * @throws ApiException When:
     *                     - The API request fails
     *                     - The response contains invalid JSON
     *                     - The response format is unexpected (missing required keys)
     *                     - Any other API-related error occurs
     */
    public function getPaginatedUsers(int $page = 1): array
    {
        Validators::validatePositiveInteger($page, 'Page');

        try {
            $data = $this->client->get("users", ['query' => ['page' => $page]]);

            Validators::validateApiResponseKeys($data, ['data', 'total', 'per_page', 'page', 'total_pages']);

            return [
                'users' => array_map(fn($user) => User::fromArray($user), $data['data']),
                'total' => $data['total'],
                'per_page' => $data['per_page'],
                'page' => $data['page'],
                'total_pages' => $data['total_pages'],
            ];
        } catch (\Exception $e) {
            throw new ApiException('Failed to fetch paginated users', 0, ['page' => $page], $e);
        }
    }

    /**
     * Creates a new user in the API with the specified name and job.
     *
     * This method validates the input parameters, makes an HTTP POST request to create
     * a new user record in the API, and returns the ID of the newly created user.
     *
     * @param string $name The full name of the user to create
     * @param string $job The job title/role of the user to create
     *
     * @return int The ID of the newly created user
     *
     * @throws InvalidArgumentException When:
     *                                 - The name parameter is empty
     *                                 - The job parameter is empty
     * @throws ApiException When:
     *                     - The API request fails
     *                     - The response contains invalid JSON
     *                     - The response format is unexpected (missing ID)
     *                     - Any other API-related error occurs
     */
    public function createUser(string $name, string $job): int
    {
        Validators::validateNonEmptyString($name, 'Name');
        Validators::validateNonEmptyString($job, 'Job');

        try {
            $data = $this->client->post("users", [
                'json' => ['name' => $name, 'job' => $job],
            ]);

            if (!isset($data['id'])) {
                throw ApiException::forInvalidResponse(json_encode($data));
            }

            return $data['id'];
        } catch (\Exception $e) {
            throw new ApiException('Failed to create user', 0, ['name' => $name, 'job' => $job], $e);
        }
    }
}
