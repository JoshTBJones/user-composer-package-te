<?php

namespace JoshuaJones\UserService\Tests;

use PHPUnit\Framework\TestCase;
use JoshuaJones\UserService\Contracts\HttpClientInterface;
use JoshuaJones\UserService\Services\UserService;
use JoshuaJones\UserService\Exceptions\ApiException;
use InvalidArgumentException;

class UserServiceTest extends TestCase
{
    public function testGetUserById()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('get')
            ->with('users/2')
            ->willReturn(['data' => ['id' => 2, 'first_name' => 'John', 'last_name' => 'Doe', 'job' => 'Developer']]);

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);
        $user = $service->getUserById(2);

        $this->assertEquals(2, $user->toArray()['id']);
    }

    public function testGetPaginatedUsers()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('get')
            ->with('users', ['query' => ['page' => 1]])
            ->willReturn([
                'data' => [['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe', 'job' => 'Developer']],
                'total' => 12,
                'per_page' => 6,
                'page' => 1,
                'total_pages' => 2
            ]);

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);
        $result = $service->getPaginatedUsers(1);

        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertEquals(1, $result['page']);
    }

    public function testCreateUser()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('post')
            ->with('users', ['json' => ['name' => 'John Doe', 'job' => 'Developer']])
            ->willReturn(['id' => 123]);

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);
        $userId = $service->createUser('John Doe', 'Developer');

        $this->assertIsInt($userId);
        $this->assertEquals(123, $userId);
    }

    public function testGetUserByIdThrowsExceptionForNonExistentUser()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('get')
            ->with('users/999')
            ->willThrowException(new ApiException('User not found', 404));

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);

        $this->expectException(ApiException::class);
        $service->getUserById(999);
    }

    public function testGetPaginatedUsersReturnsEmptyList()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('get')
            ->with('users', ['query' => ['page' => 1]])
            ->willReturn([
                'data' => [],
                'total' => 0,
                'per_page' => 6,
                'page' => 1,
                'total_pages' => 0
            ]);

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);
        $result = $service->getPaginatedUsers(1);

        $this->assertEmpty($result['users']);
    }

    public function testGetPaginatedUsersWithInvalidPage()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('get')
            ->with('users', ['query' => ['page' => 0]])
            ->willReturn([
                'data' => [['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe', 'job' => 'Developer']],
                'total' => 12,
                'per_page' => 6,
                'page' => 0,
                'total_pages' => 2
            ]);

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);

        $this->expectException(InvalidArgumentException::class);
        $service->getPaginatedUsers(0);
    }

    public function testCreateUserThrowsExceptionOnFailure()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('post')
            ->with('users', ['json' => ['name' => 'John Doe', 'job' => 'Developer']])
            ->willThrowException(new ApiException('HTTP POST request failed'));

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);

        $this->expectException(ApiException::class);
        $service->createUser('John Doe', 'Developer');
    }

    public function testGetUserByIdHandlesInvalidJson()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('get')
            ->with('users/2')
            ->willReturn(['invalid' => 'json']);

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);

        $this->expectException(ApiException::class);
        $service->getUserById(2);
    }

    public function testGetUserByIdHandlesNetworkTimeout()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('get')
            ->with('users/2')
            ->willThrowException(new ApiException('Network timeout'));

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);

        $this->expectException(ApiException::class);
        $service->getUserById(2);
    }

    public function testCreateUserWithInvalidData()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('post')
            ->with('users', ['json' => ['name' => '', 'job' => '']])
            ->willThrowException(new ApiException('Invalid data'));

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);

        $this->expectException(InvalidArgumentException::class);
        $service->createUser('', '');
    }

    public function testCreateUserWithNoData()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('post')
            ->with('users')
            ->willThrowException(new ApiException('Invalid data'));

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);

        $this->expectException(InvalidArgumentException::class);
        $service->createUser('', '');
    }

    public function testGetUserByIdWithLargeId()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('get')
            ->with('users/999999999')
            ->willThrowException(new ApiException('User not found', 404));

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);

        $this->expectException(ApiException::class);
        $service->getUserById(999999999);
    }

    public function testCreateUserWithEmptyNameOrJob()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->method('post')
            ->with('users', ['json' => ['name' => '', 'job' => 'Developer']])
            ->willThrowException(new ApiException('Name cannot be empty'));

        /** @var HttpClientInterface $mockHttpClient */
        $service = new UserService($mockHttpClient);

        $this->expectException(InvalidArgumentException::class);
        $service->createUser('', 'Developer');
    }
}
