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

namespace Tests\ApiPlatformLaravel\Unit\Bridge;

use ApiPlatformLaravel\Bridge\UrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as SymfonyUrlGenerator;

class UrlGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testCurrent()
    {
        $routes = $this->createMock(RouteCollection::class);
        $request = new Request();
        $symfonyGenerator = $this->createMock(SymfonyUrlGenerator::class);
        $route = $this->createMock(Route::class);

        $symfonyGenerator->expects($this->at(0))
            ->method('generate')
            ->with('test', [], SymfonyUrlGenerator::ABSOLUTE_URL)
            ->willReturn('/api/tests');
        $symfonyGenerator->expects($this->at(1))
            ->method('generate')
            ->with('not_found')
            ->willThrowException(new RouteNotFoundException());

        $routes->expects($this->once())
            ->method('getByName')
            ->with('not_found')
            ->willReturn($route);

        $route
            ->method('getDomain')
            ->willReturn('localhost');
        $route->method('uri')->willReturn('/laravel');

        $generator = new UrlGenerator($routes, $request);
        $generator->setSymfonyGenerator($symfonyGenerator);
        $this->assertEquals('/api/tests', $generator->route('test'));
        $this->assertEquals('/laravel#', $generator->route('not_found', [], false));
    }
}
