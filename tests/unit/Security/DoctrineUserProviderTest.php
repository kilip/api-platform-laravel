<?php

/*
 * This file is part of the Api Platform Laravel project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\ApiPlatformLaravel\Unit\Security;

use ApiPlatformLaravel\Security\DoctrineUserProvider;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher;
use PHPUnit\Framework\TestCase;
use Tests\Dummy\Model\User;

class DoctrineUserProviderTest extends TestCase
{
    /**
     * @var DoctrineUserProvider
     */
    private $provider;

    /**
     * @var Hasher|\PHPUnit\Framework\MockObject\MockObject
     */
    private $hasher;

    /**
     * @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $manager;

    /**
     * @var ObjectRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    /**
     * @var string
     */
    private $model;

    /**
     * @var array
     */
    private $identifiers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = $this->createMock(Hasher::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->repository = $this->createMock(ObjectRepository::class);
        $this->model = User::class;
        $this->identifiers = [
            'username',
            'id',
            'email',
        ];

        $this->manager
            ->method('getRepository')
            ->with($this->model)
            ->willReturn($this->repository);

        $this->provider = new DoctrineUserProvider(
            $this->hasher,
            $this->manager,
            $this->model,
            $this->identifiers
        );
    }

    /**
     * @dataProvider getTestRetrieveByIdData()
     *
     * @param string $identifier
     * @param string $value
     */
    public function testRetrieveById($identifier, $value)
    {
        $provider = $this->provider;
        $user = $this->createMock(Authenticatable::class);
        $repository = $this->repository;

        $mapValue = [
            'username' => null,
            'email' => null,
            'id' => null,
        ];

        // set value for  expected identifier
        $mapValue[$identifier] = $user;

        $repository
            ->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->willReturnMap([
                [
                    ['username' => $value],
                    $mapValue['username'],
                ],
                [
                    ['email' => $value],
                    $mapValue['email'],
                ],
                [
                    ['id' => $value],
                    $mapValue['id'],
                ],
            ]);

        $this->assertInstanceOf(Authenticatable::class, $provider->retrieveById($value));
    }

    public function getTestRetrieveByIdData()
    {
        return [
            ['username', 'test-username'],
            ['email', 'test@example.com'],
            ['id', 'test@id'],
        ];
    }

    public function testRetrieveByToken()
    {
        $repository = $this->repository;
        $provider = $this->provider;
        $authenticatable = $this->createMock(Authenticatable::class);

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'id' => 'some-id',
                'rememberToken' => 'some-token',
            ])
            ->willReturn($authenticatable);

        $this->assertSame($authenticatable, $provider->retrieveByToken('some-id', 'some-token'));
    }

    public function testUpdateRememberToken()
    {
        $manager = $this->manager;
        $provider = $this->provider;
        $authenticatable = $this->createMock(Authenticatable::class);

        $authenticatable->expects($this->once())
            ->method('setRememberToken')
            ->with('some-token');

        $manager->expects($this->once())
            ->method('persist')
            ->with($authenticatable);
        $manager->expects($this->once())
            ->method('flush');

        $provider->updateRememberToken($authenticatable, 'some-token');
    }

    public function testRetrieveByCredentials()
    {
        $repository = $this->repository;
        $provider = $this->provider;
        $authenticatable = $this->createMock(Authenticatable::class);

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'some-token'])
            ->willReturn($authenticatable);

        $provider->retrieveByCredentials(['token' => 'some-token']);
    }

    public function testValidateCredentials()
    {
        $hasher = $this->hasher;
        $provider = $this->provider;
        $authenticatable = $this->createMock(Authenticatable::class);

        $authenticatable->expects($this->once())
            ->method('getAuthPassword')
            ->willReturn('auth-password');

        $hasher->expects($this->once())
            ->method('check')
            ->with('user-password', 'auth-password')
            ->willReturn(true);

        $this->assertTrue($provider->validateCredentials($authenticatable, ['password' => 'user-password']));
    }
}
