<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Http\Exception;

use RuntimeException;

class HttpException extends RuntimeException
{
    /**
     * Http status code
     *
     * @var int
     */
    private $statusCode;

    /**
     * Http header status message
     *
     * @var array
     */
    private static $statusCodeMessage = [
        200 => 'HTTP/1.1 200 OK',
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        500 => 'HTTP/1.1 500 Internal Server Error',
        502 => 'HTTP/1.1 502 Bad Gateway',
        503 => 'HTTP/1.1 503 Service Unavailable',
        503 => 'HTTP/1.1 504 Gateway Time-out',
    ];

    public function __construct($statusCode, $message)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getStatusCodeMessage()
    {
        if (isset(self::$statusCodeMessage[$this->statusCode])) {
            return self::$statusCodeMessage[$this->statusCode];
        }
        return;
    }
}

