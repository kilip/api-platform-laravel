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

namespace ApiPlatformLaravel;

class Events
{
    public const BOOT = 'api_platform.boot';
    public const KERNEL_BOOT = 'api_platform.kernel.boot';
}
