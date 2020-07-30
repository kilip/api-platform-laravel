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

namespace ApiPlatformLaravel\Http\Controllers;

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Request;

class PlaceholderAction extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api_platform')->except(function ($request) {
            return false;
        });
        $this->middleware('api_platform');
    }

    public function __invoke(Request $request)
    {
        return $request;
    }
}
