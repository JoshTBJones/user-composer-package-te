<?php

namespace JoshuaJones\UserService\Dto;

/**
 * Data Transfer Object (DTO) representing a user entity.
 *
 * This class encapsulates user data and provides methods for converting between
 * array representations and object instances. It implements JsonSerializable to
 * enable proper JSON encoding of User objects.
 *
 * The User DTO contains:
 * - ID: Unique identifier for the user
 * - Name: User's full name (combination of first and last name)
 * - Job: User's job title/role
 *
 * Key features:
 * - Immutable properties via private constructor parameters
 * - Static factory method for array to object conversion
 * - Methods for array/JSON serialization
 * - Input validation on construction
 *
 * @package JoshuaJones\UserService\Dto
 *
 * @see \JsonSerializable For JSON serialization contract
 *
 * @property-read int $id The user's unique identifier
 * @property-read string $name The user's full name
 * @property-read string $job The user's job title
 */

class User implements \JsonSerializable
{
    public function __construct(
        private int $id,
        private string $name,
        private string $job,
    ) {
    }

    /**
     * Creates a new User instance from an array of data.
     *
     * @param array $data The array containing user data with the following keys:
     *                    - id (int): The user's ID
     *                    - first_name (string): The user's first name
     *                    - last_name (string): The user's last name
     *                    - job (string|null): The user's job title (optional)
     *
     * @return self A new User instance populated with the array data
     *
     * @throws \InvalidArgumentException If required array keys are missing or invalid
     */
    public static function fromArray(array $data): self
    {
        return new self($data['id'], $data['first_name'] . ' ' . $data['last_name'], $data['job'] ?? '');
    }

    /**
     * Converts the User object to an associative array.
     *
     * @return array An array containing the following user data:
     *               - id (int): The user's ID
     *               - name (string): The user's full name
     *               - job (string): The user's job title
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'job' => $this->job,
        ];
    }

    /**
     * Specifies the data that should be serialized to JSON when calling json_encode().
     * Implements the JsonSerializable interface.
     *
     * @return array An array containing the following user data:
     *               - id (int): The user's ID
     *               - name (string): The user's full name
     *               - job (string): The user's job title
     *
     * @see \JsonSerializable::jsonSerialize()
     * @see User::toArray()
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
