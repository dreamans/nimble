<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\MongoDB\Exception;

use RuntimeException;

class UnacknowledgedException extends RuntimeException 
{
    public static function writeResultAccess($message)
    {
        return new static($message);
    }
}

