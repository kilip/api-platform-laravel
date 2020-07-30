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

namespace ApiPlatformLaravel\Compat;

use Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator as BaseAbstractConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

if (version_compare(BaseKernel::VERSION, '5', '<')) {
    class AbstractConfigurator extends BaseAbstractConfigurator
    {
        public static $valuePreProcessor;
    }
} else {
    class AbstractConfigurator extends BaseAbstractConfigurator
    {
    }
}
