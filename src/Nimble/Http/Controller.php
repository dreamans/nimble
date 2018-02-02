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
use Nimble\Http\Exception\HttpRuntimeException;

class Controller
{
    /**
     * @var string
     */
    private $controllerPre;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var bool
     */
    private $terminate = false;

    /**
     * @var object Application
     */
    private $app;

    /**
     * @var string
     */
    private $controllerClass;

    /**
     * @param  string $controllerPre
     * @param  string $viewPath
     */
    public function __construct(Application $app, $controllerPre, $controllerClass, $viewPath) 
    {
        $this->controllerPre = $controllerPre;
        $this->controllerClass = $controllerClass;
        $arrContainer = [
            'view' => $this->view($viewPath),
            'app' => $app,
            'request' => $app->request,
            'response' => $app->response,
        ];
        $this->container = new Container($arrContainer);
    }

    /**
     * @return Container
     *
     * @throws HttpRuntimeException
     */
    public function runController()
    {
        $controller = $this->container->controller = "{$this->controllerPre}{$this->controllerClass}";
        if (!class_exists($controller)) {
            throw new HttpRuntimeException(sprintf("Controller class \"{$controller}\" not exists"));
        }
        $this->controller()->afterController();
        return $this->container;
    }

    private function controller()
    {
        if (true === $this->terminate) {
            return $this;
        }
        $ctlObject = new $this->container->controller($this->container);

        if (method_exists($ctlObject, 'request')) {
            call_user_func([$ctlObject, 'request'], $this->container->request);
        }

        if (method_exists($ctlObject, 'main')) {
            call_user_func([$ctlObject, 'main']);
        }

        if (method_exists($ctlObject, 'response')) {
            $this->container->content = call_user_func([$ctlObject, 'response']);
        }

        $this->terminate = true;

        return $this;
    }

    private function afterController()
    {
        $this->container->response->content($this->container->content);
    }

    private function view($path)
    {
        return View::getViewObject($path);
    }
}

