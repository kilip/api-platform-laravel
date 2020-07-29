<?php


namespace Tests\Dummy\Model;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use LaravelDoctrine\ORM\Auth\Authenticatable as AuthenticatableTrait;

/**
 * Class User
 *
 * @ORM\Entity()
 * @ApiResource()
 * @package Tests\Dummy\Entity
 */
class User implements AuthenticatableContract
{
    use AuthenticatableTrait;

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