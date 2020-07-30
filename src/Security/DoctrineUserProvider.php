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

namespace ApiPlatformLaravel\Security;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;

class DoctrineUserProvider implements UserProviderContract
{
    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var string
     */
    private $model;
    /**
     * @var array
     */
    private $identifiers;

    /**
     * DoctrineUserProvider constructor.
     *
     * @param Hasher        $hasher
     * @param ObjectManager $manager
     * @param string        $model
     * @param array         $identifiers
     */
    public function __construct(
        Hasher $hasher,
        ObjectManager $manager,
        string $model,
        array $identifiers
    ) {
        $this->hasher = $hasher;
        $this->manager = $manager;
        $this->model = $model;
        $this->identifiers = $identifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveById($identifier)
    {
        $repository = $this->getRepository();
        $authenticatable = null;
        foreach ($this->identifiers as $column) {
            $found = $repository->findOneBy([$column => $identifier]);
            if ($found instanceof Authenticatable) {
                $authenticatable = $found;
            }
        }

        return $authenticatable;
    }

    public function retrieveByToken($identifier, $token)
    {
        $object = (new \ReflectionClass($this->model))->newInstanceWithoutConstructor();

        return $this->getRepository()->findOneBy([
            $object->getAuthIdentifierName() => $identifier,
            $object->getRememberTokenName() => $token,
        ]);
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        $manager = $this->manager;

        $user->setRememberToken($token);
        $manager->persist($user);
        $manager->flush();
    }

    public function retrieveByCredentials(array $credentials)
    {
        $criteria = [];
        foreach ($credentials as $key => $value) {
            if (!Str::contains($key, 'password')) {
                $criteria[$key] = $value;
            }
        }

        return $this->getRepository()->findOneBy($criteria);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->hasher->check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * @return ObjectRepository
     */
    private function getRepository()
    {
        return $this->manager->getRepository($this->model);
    }
}
