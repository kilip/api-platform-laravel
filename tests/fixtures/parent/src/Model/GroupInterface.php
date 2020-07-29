<?php


namespace Tests\Parent\Model;


interface GroupInterface
{
    /**
     * @param string $name
     * @return static
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();
}