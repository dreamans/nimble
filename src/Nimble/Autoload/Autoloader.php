<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Autoload;

class Autoloader
{
    private static $loader = null;

    public static function initLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }
        self::checkPhpVersion();
        self::$loader = $loader = self::loadClassLoader();

        $arrPsr4Prefix = require __DIR__ . '/psr-4.php';
        foreach($arrPsr4Prefix as $nsPre => $pathPre) {
            $loader->setPrefix($nsPre, $pathPre);
        }

        $loader->register();
        return $loader;
    }

    private static function loadClassLoader()
    {
        $classLoaderFile = __DIR__ . '/ClassLoader.php';
        if (is_file($classLoaderFile)) {
            require_once $classLoaderFile;
        }
        return new \Nimble\Autoload\ClassLoader();
    }

    private static function checkPhpVersion()
    {
        if (PHP_VERSION_ID < 50600) {
            trigger_error('Nimble require PHP version >= 5.6.0', E_USER_ERROR);
        }
    }
}

