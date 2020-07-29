<?php


namespace Tests\Parent\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Permission
 * 
 * @ORM\MappedSuperclass()
 * @package Tests\Parent\Model
 */
abstract class Permission implements PermissionContract
{
    protected $name;
}