<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Http;

use Nimble\Foundation\Container;
use Nimble\Http\Exception\NotFoundException;
use RuntimeException;

class Router  
{
    /**
     * @var array
     */
    private $routerMap;

    /**
     * @var string
     */
    private $routerCb;

    /**
     * @var array
     */
    private $config;

    /**
     * routerMap
     *   
     *     '{REQUEST_URI}' => 'ActionController'
     *  or
     *     '{REQUEST_URI}' => [
     *          '{REQUEST_SON_URI}' => 'ActionController'
     *     ]
     *
     *
     * @param  callable $routerCb
     * @param  array    $routerMap
     */
    public function __construct($routerCb, array $routerMap = [])
    {
        $this->routerMap = $routerMap;
        $this->routerCb = $routerCb;

        if (!is_callable($this->routerCb)) {
            throw new RuntimeException(sprintf('$routerCb "%s" must can be called as a function', $routerCb));
        }
        $this->setRouterConfig();
    }

    /**
     * @param  Application $app
     *
     * @return Container
     *
     * @throws NotFoundException
     */
    public function parseWebRouter(Application $app)
    {
        $requestRouterPath = call_user_func_array($this->routerCb, [$app]);
        foreach ($this->config as $url => $ctl) {
            if ($url === $requestRouterPath) {
                return $ctl;
            }
        }
        throw new NotFoundException(sprintf("Router Page \"%s\" Not Found", $requestRouterPath));
    }

    private function setRouterConfig()
    {
        $arrRoute2Ctl = [];
        foreach($this->routerMap as $uri => $map) {
            if (is_array($map)) {
                foreach($map as $suri => $smap) {
                    $urlPath = "{$uri}/{$suri}";
                    $arrRoute2Ctl[$urlPath] = $smap;
                }
            } else {
                $arrRoute2Ctl[$uri] = $map;
            }
        }
        $config = [];
        foreach($arrRoute2Ctl as $uri => $map) {
            $urlPath = $this->formatConfigPath($uri);
            $config[$urlPath] = $map;
        }
        $this->config = $config;
    }

    /**
     * @param  string $url
     *
     * @return string
     */
    private function formatConfigPath($url)
    {
        $url = '/'. strtolower(trim($url, " \t\n\r\0\x0B/"));
        return $url;
    }
}
