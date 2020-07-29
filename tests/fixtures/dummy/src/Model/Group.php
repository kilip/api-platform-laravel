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

namespace Tests\Dummy\Model;

use Doctrine\ORM\Mapping as ORM;
use Tests\Parent\Model\GroupInterface;
use Tests\Parent\Model\GroupTrait;

/**
 * Class Group.
 *
 * @ORM\Entity
 * @ORM\Table(name="groups")
 */
class Group implements GroupInterface
{
    use GroupTrait;

    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     *
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
