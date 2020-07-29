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

use ApiPlatformLaravel\Model\AuthenticableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * User trait
 */
trait UserTrait
{
    use AuthenticableTrait;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $fullname;

    /**
     * @ORM\ManyToOne(targetEntity="Tests\Parent\Model\GroupInterface")
     * @var GroupInterface
     */
    protected $group;

    /**
     * {@inheritDoc}
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return static
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return static
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullname(): string
    {
        return $this->fullname;
    }

    /**
     * @param string $fullname
     * @return static
     */
    public function setFullname(string $fullname)
    {
        $this->fullname = $fullname;
        return $this;
    }
}
