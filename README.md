# PHP User Service Client

A PHP client library for interacting with a User API service. This package provides a clean, type-safe interface for managing user data through a RESTful API.

## Installation

Install via Composer:
```bash
composer require joshua-jones/user-service
```
note: this package is not available on packagist, this is just an example.

## Usage

### Initialization

To use the `UserService`, instantiate it with an optional custom HTTP client. By default, it uses the `GuzzleHttpClient`.
```php
use UserService\UserService;
use GuzzleHttp\Client;

$client = new Client();
$userService = new UserService($client);
```
### Retrieving a User

To retrieve a user by their ID, use the `getUser` method:
```php
try {
    $user = $userService->getUserById(2);
    echo $user->toArray()['name']; // Outputs the user's name
} catch (ApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Fetch Paginated Users

Retrieve a paginated list of users with the `getPaginatedUsers` method. This returns an associative array with user data and pagination metadata.

```php
try {
    $result = $userService->getPaginatedUsers(1);
    foreach ($result['users'] as $user) {
        echo $user->toArray()['name'] . "\n";
    }
} catch (ApiException $e) {
    echo "Error: " . $e->getMessage();
}   
```


### Create a New User

Create a new user using the `createUser` method. This returns the ID of the newly created user.

```php
try {
    $userId = $userService->createUser('John Doe', 'Developer');
    echo "New user ID: " . $userId;
} catch (ApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

This package provides a robust interface for interacting with the User API, with built-in error handling and validation to ensure data integrity.


## Exception Handling

The package uses custom exceptions to handle errors:

- `ApiException`: Thrown for API-related errors.
- `InvalidArgumentException`: Thrown for input validation errors.


## User Data Transfer Object (DTO)

The `User` DTO represents a user entity and provides methods for converting between array representations and object instances. It implements `JsonSerializable` to enable proper JSON encoding of User objects.

### Properties

- **ID**: Unique identifier for the user.
- **Name**: User's full name (combination of first and last name).
- **Job**: User's job title/role.

### Methods

- **`__construct(int $id, string $name, string $job)`**: Initializes a new User object with the given ID, name, and job.
- **`static fromArray(array $data): User`**: Creates a User object from an associative array.
- **`toArray(): array`**: Converts the User object to an associative array.
- **`jsonSerialize(): array`**: Prepares the User object for JSON serialization.

### Example
```php
use JoshuaJones\UserService\Dto\User;
// Creating a User object from an array
$userData = ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe', 'job' => 'Developer'];
$user = User::fromArray($userData);
// Accessing User properties
echo $user->toArray()['name']; // Outputs: John Doe
```

## Testing

The package includes a suite of PHPUnit tests. To run the tests, execute the following command:

```bash
vendor/bin/phpunit
```

This will execute the tests and provide a detailed report of the results.
