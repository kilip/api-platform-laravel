<?php


namespace Tests\Parent\Model;


use Doctrine\ORM\Mapping as ORM;

trait GroupTrait
{
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $name;

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }
}