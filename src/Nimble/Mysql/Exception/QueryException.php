<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Mysql\Exception;

use RuntimeException;

class QueryException extends RuntimeException 
{
    public static function executeSql(array $errorInfo, $sql)
    {
        $message = sprintf('Mysql query error: %s, SQL: [ %s ]', $errorInfo[2], $sql);
        return new static($message);
    }

    public static function transaction($message)
    {
        $message = sprintf("Mysql translation error: %s", $message);
        return new static($message);
    }
}

