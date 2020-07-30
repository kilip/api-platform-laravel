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

use Symfony\Component\HttpKernel\Kernel as BaseKernel;

if (version_compare(BaseKernel::VERSION, '5', '<')) {
    abstract class CompatKernel extends BaseKernel
    {
        public function locateResource($name, $dir = null, $first = true)
        {
            return $this->doLocateResource($name);
        }

        abstract protected function doLocateResource($name);
    }
} else {
    abstract class CompatKernel extends BaseKernel
    {
        public function locateResource(string $name)
        {
            return $this->doLocateResource($name);
        }

        abstract protected function doLocateResource($name);
    }
}
