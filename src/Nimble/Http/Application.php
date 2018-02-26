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
use Nimble\Foundation\Bootstrap;

class Application
{
    /**
     * @var Bootstrap
     */
    private $boot;

    /**
     * @var Response
     */
    public $response;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var bool
     */
    private $appIsStarted;

    /**
     * @var Configure
     */
    private $configure;

    /**
     * @param  Bootstrap $boot
     */
    public function __construct(Bootstrap $boot)
    {
        $this->boot = $boot;
    }

    /**
     * @return Bootstrap
     */
    public function bootstrap()
    {
        return $this->boot;
    }

    /**
     * @param  string $appConfig
     *
     * @return object $this
     */
    public function startApplication($appConfig = 'app')
    {
        if (true === $this->appIsStarted) {
            return $this;
        }

        $this->appIsStarted = true;

        $this->configure = $this->boot->configureObject();
        $this->configure->setConfig('app', $this->configure->getConfig($appConfig));

        $this->initApplication();
        $this->startWebApplication();

        return $this;
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function configure($key)
    {
        return $this->configure->getConfig($key);
    }

    private function initApplication()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    private function startWebApplication()
    {
        $ctlClass = $this->webRouter();

        $ctlPre = $this->configure->getConfig('app.controller.pre');
        $viewPath = $this->configure->getConfig('app.view.path');
        $controller = new Controller($this, $ctlPre, $ctlClass, $viewPath);
        $controllerContainer = $controller->runController();
        $controllerContainer->response->respContent();
    }

    public function terminate()
    {
        $this->terminateWebApplication();
    }

    private function terminateWebApplication()
    {
        exit;
    }

    private function webRouter()
    {
        $routerCb = $this->configure->getConfig('app.router.callback');
        $routerMap = $this->configure->getConfig('app.router.map');
        $routerMap = is_array($routerMap) ? $routerMap : [];
        $router = new Router($routerCb, $routerMap);

        return $router->parseWebRouter($this);
    }
}
