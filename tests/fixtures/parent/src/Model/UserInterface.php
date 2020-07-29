<?php


namespace Tests\Parent\Model;


use ApiPlatformLaravel\Contracts\UserInterface as UserContract;
use Tests\Dummy\Model\Group;

interface UserInterface extends UserContract
{
    /**
     * @param string $username
     * @return static
     */
    public function setUsername(string $username);

    /**
     * @return string
     */
    public function getUsername();

    /**
     * @param string $fullname
     * @return static
     */
    public function setFullname(string $fullname);

    /**
     * @return string
     */
    public function getFullname();

    /**
     * @param string $email
     * @return static
     */
    public function setEmail(string $email);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param GroupInterface $group
     * @return static
     */
    public function setGroup($group);

    /**
     * @return GroupInterface
     */
    public function getGroup();
}