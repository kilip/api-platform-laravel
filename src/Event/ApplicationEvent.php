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

namespace ApiPlatformLaravel\Event;

use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ApplicationEvent
{
    /**
     * @var Application
     */
    private $application;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        Application $application
    ) {
        $this->application = $application;
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return ApplicationEvent
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
