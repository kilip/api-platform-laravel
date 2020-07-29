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

namespace Tests\Parent\Model;

use ApiPlatformLaravel\Contracts\UserInterface as UserContract;

interface UserInterface extends UserContract
{
    /**
     * @param string $username
     *
     * @return static
     */
    public function setUsername(string $username);

    /**
     * @return string
     */
    public function getUsername();

    /**
     * @param string $fullname
     *
     * @return static
     */
    public function setFullname(string $fullname);

    /**
     * @return string
     */
    public function getFullname();

    /**
     * @param string $email
     *
     * @return static
     */
    public function setEmail(string $email);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param GroupInterface $group
     *
     * @return static
     */
    public function setGroup($group);

    /**
     * @return GroupInterface
     */
    public function getGroup();
}
