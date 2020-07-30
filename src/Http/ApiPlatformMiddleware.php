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

namespace ApiPlatformLaravel\Http;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApiPlatformMiddleware
{
    /**
     * @var HttpKernelInterface
     */
    private $kernel;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        HttpKernelInterface $kernel,
        SessionInterface $session = null
    ) {
        $this->kernel = $kernel;
        $this->session = $session;
    }

    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        if (null !== $this->session) {
            $request->setSession($this->session);
        }

        return $this->kernel->handle($request);
    }
}
