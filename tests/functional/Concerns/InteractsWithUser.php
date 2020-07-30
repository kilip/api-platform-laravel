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

namespace Tests\ApiPlatformLaravel\Functional\Concerns;

use Illuminate\Support\Facades\Hash;
use Tests\Dummy\Model\Group;
use Tests\Dummy\Model\User;

trait InteractsWithUser
{
    /**
     * @param string $username
     * @param string $email
     * @param string $fullname
     * @param string $password
     *
     * @return User
     */
    protected function createUser($username = 'test', $email = 'test@example.com', $fullname = 'Test User', $password = 'test')
    {
        $model = config('auth.providers.users.model');
        $user = $this->getRepository($model)->findOneBy(['username' => $username]);

        if (!\is_object($user)) {
            $user = new User();
            $user
                ->setUsername($username)
                ->setEmail($email)
                ->setFullname($fullname)
                ->setPassword(Hash::make($password));
            $this->store($user);
        }

        return $user;
    }

    /**
     * @param string $name
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return Group
     */
    protected function createGroup($name = 'test')
    {
        $group = $this->getRepository(Group::class)->findBy(['name' => $name]);
        if (!\is_object($group)) {
            $group = new Group();
            $group->setName($name);
            $this->store($group);
        }

        return $group;
    }
}
