<?php


namespace Tests\Dummy\Model;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Tests\Parent\Model\User as BaseUser;

/**
 * Class User
 *
 * @ORM\Entity()
 * @ApiResource()
 * @package Tests\Dummy\Entity
 */
class User extends BaseUser
{
    /**
     * @ORM\Column(type="string")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    protected $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}