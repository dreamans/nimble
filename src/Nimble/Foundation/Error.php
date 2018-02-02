<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Dreamans <dreamans@163.com>
 */
namespace Nimble\Foundation;

use ErrorException;
use Exception;
use Nimble\Foundation\ExceptionHandle;

class Error 
{
    /**
     * @var string 
     */
    private $userHandle;

    /**
     * @param  string $userHandle 
     */
    public function __construct($userHandle)
    {
        $this->userHandle = $userHandle;

        $this->handle();
    }

    private function handle()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'off');

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * @param  int    $level
     * @param  string $message
     * @param  string $file
     * @param  int    $line
     * @param  array  $context
     *
     * @throws ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * @param  Exception $e
     *
     * @throws Exception
     * @throws userHandle
     */
    public function handleException($e)
    {
        $handle = new $this->userHandle($e);
        if ($handle instanceof ExceptionHandle) {
            $handle->render();
        }
    }

    /**
     * @throws ErrorException
     */
    public function handleShutdown()
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
        }
    }

    /**
     * @param  int $type
     *
     * @return bool
     */
    private function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }
}

