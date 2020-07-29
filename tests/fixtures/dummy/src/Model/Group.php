<?php


namespace Tests\Dummy\Model;


use Doctrine\ORM\Mapping as ORM;
use Tests\Parent\Model\GroupInterface;
use Tests\Parent\Model\GroupTrait;

/**
 * Class Group
 * @ORM\Entity()
 * @ORM\Table(name="groups")
 * @package Tests\Dummy\Model
 */
class Group implements GroupInterface
{
    use GroupTrait;

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
    public function getId()
    {
        return $this->id;
    }
}