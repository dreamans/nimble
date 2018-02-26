<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Foundation;

use Nimble\Foundation\ExceptionHandle;
use RuntimeException;

class Bootstrap 
{
    /**
     * Configure object
     *
     * @var object
     */
    private $configure;

    /**
     * @var object
     */
    private $bootConfig;

    /**
     * @param  array       $bootConfig
     * @param  string|null $appClassName
     *
     * @return object
     */
    public static function application(array $bootConfig = [], $appClassName = null)
    {
        $boot = new Bootstrap($bootConfig);
        if (null === $appClassName) {
            return $boot;
        }
        if (!class_exists($appClassName)) {
            throw new RuntimeException(sprintf('App class "%s" not exists', $appClassName));
        }
        return new $appClassName($boot);
    }

    /**
     * Boot Configure
     * 
     *  * $config['app_path'] - Application path
     *  
     *  * $config['config_path'] - Application configuration file path
     *
     *  * $config['user_exception_handle'] - user defined exception handle class
     *
     * @param  array $config
     */
    public function __construct(array $config = [])
    {
        $this->initBootConfig($config);

        $this->checkEnv();

        $this->bootstrap();
    }

    /**
     * @param  string $key
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function configure($key)
    {
        return $this->configure->getConfig($key);
    }

    public function configureObject()
    {
        return $this->configure;
    }

    public function nimbleVersion()
    {
        return Env::nimbleVersion();
    }

    public function phpVersion($isInt = false)
    {
        return Env::phpVersion($isInt);
    }

    /**
     * @param  array $config
     */
    private function initBootConfig(array $config)
    {
        foreach ($config as $key => $val) {
            $newKey = preg_replace_callback('/([-_]+([a-z]{1}))/i', function($m){
                return strtoupper($m[2]);
            }, $key);
            unset($config[$key]);
            $config[$newKey] = $val;
        }

        $bootConfig = new Container($config);
        if (!$bootConfig->appPath) {
            $bootConfig->appPath = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'app';
        }
        if (!$bootConfig->configPath) {
            $bootConfig->configPath = $bootConfig->appPath .  DIRECTORY_SEPARATOR . 'config';
        }
        if (!$bootConfig->userExceptionHandle || !class_exists($bootConfig->userExceptionHandle)) {
            $bootConfig->userExceptionHandle = ExceptionHandle::class;
        }
    
        $this->bootConfig = $bootConfig;
    }

    private function checkEnv()
    {
        Env::checkPhpVersion();
    }

    private function bootstrap()
    {
        $this->regException();
        $this->regConfig();
    }

    private function regConfig()
    {
        $this->configure = new Configure($this->bootConfig->configPath);
    }

    private function regException()
    {
        new Error($this->bootConfig->userExceptionHandle);
    }
}
