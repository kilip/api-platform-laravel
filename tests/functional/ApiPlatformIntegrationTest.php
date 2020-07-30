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

namespace Tests\ApiPlatformLaravel\Functional;

use Illuminate\Routing\Route;

class ApiPlatformIntegrationTest extends TestCase
{
    public function testResource()
    {
        $url = route('api_users_get_collection', [], true);
        $this->assertNotNull($url);
        $this->assertEquals('http://localhost/users', $url);
    }
}
