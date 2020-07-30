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

namespace ApiPlatformLaravel\Bridge;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use Illuminate\Routing\UrlGenerator as LaravelUrlGenerator;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as SymfonyUrlGenerator;

class UrlGenerator extends LaravelUrlGenerator
{
    private $requestTypeMap = [
        true => UrlGeneratorInterface::ABS_URL,
        false => UrlGeneratorInterface::ABS_PATH,
    ];
    /**
     * @var SymfonyUrlGenerator
     */
    private $symfonyGenerator;

    /**
     * @param SymfonyUrlGenerator $generator
     */
    public function setSymfonyGenerator(SymfonyUrlGenerator $generator)
    {
        $this->symfonyGenerator = $generator;
    }

    public function route($name, $parameters = [], $absolute = true)
    {
        try {
            return $this->symfonyGenerator->generate($name, $parameters, $this->requestTypeMap[$absolute]);
        } catch (RouteNotFoundException $exception) {
            return parent::route($name, $parameters, $absolute); // TODO: Change the autogenerated stub
        }
    }
}
