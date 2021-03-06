<?php
/**
 * Moon framework
 *
 * @author      Mohamed Aymen Ben Slimane <aymen.kernel@gmail.com>
 * @copyright   2015 Mohamed Aymen Ben Slimane
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Mohamed Aymen Ben Slimane
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Moon\Router;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
{
    protected $routeCollection = array();

    public static $httpMethodes = array(
        Request::METHOD_POST,
        Request::METHOD_HEAD,
        Request::METHOD_GET,
        Request::METHOD_POST,
        Request::METHOD_PUT,
        Request::METHOD_PATCH,
        Request::METHOD_DELETE,
        Request::METHOD_PURGE,
        Request::METHOD_OPTIONS,
        Request::METHOD_TRACE,
        Request::METHOD_CONNECT,
    );

    public function __construct()
    {
    }

    public function mount($route, $methodes = array())
    {
        if (empty($methodes)) {
            $this->routeCollection[$route->getName()] = $route;
        } else {
            foreach ($methodes as $methode) {
                $key = $this->getUniqueRouteKey($methode, $route->getName());
                $this->routeCollection[$key] = $route;
            }
        }
    }

    public function handleRequest(Request $request)
    {
        $methode = $request->getMethod();
        $path = $request->getPathInfo();

        foreach ($this->routeCollection as $route) {

            if ($this->match($path, $route)) {

                if (isset($this->routeCollection[$route->getName()])) {
                    return $this->executeController($this->routeCollection[$route->getName()]->getCallback(), $request);
                }

                $key = $this->getUniqueRouteKey($methode, $route->getName());
                if (isset($this->routeCollection[$key])) {
                    return $this->executeController($this->routeCollection[$key]->getCallback(), $request);
                }
            }
        }

        return new Response('404 Not Found', 404);
    }

    protected function executeController($controller, $request)
    {
        return call_user_func_array($controller, array($request));
    }

    protected function match($currentPath, $route)
    {
        $currentPath = trim($currentPath, '/');
        $routePath = trim($route->getPath(), '/');

        if ($currentPath == $routePath) {
            return true;
        }
        return false;
    }

    private function getUniqueRouteKey($methode, $routeName)
    {
        if (empty($methode)) {
            return $routeName;
        }
        return strtolower($methode).'::'.$routeName;
    }
}
 