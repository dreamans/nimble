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

class Env 
{
    const NIMBLE_VERSION = '0.9.0';

    /**
     * @return trigger_error
     */
    public static function checkPhpVersion()
    {
        if (self::phpVersion(true) < 50600) {
            trigger_error('Nimble require PHP version >= 5.6.0', E_USER_ERROR);
        }
    }

    /**
     * @return string
     */
    public static function nimbleVersion()
    {
        return self::NIMBLE_VERSION;
    }

    /**
     * @param  bool
     *
     * @return int|string
     */
    public static function phpVersion($isInt = false)
    {
        if ($isInt) {
            return PHP_VERSION_ID;
        }
        return phpversion();
    }
}
