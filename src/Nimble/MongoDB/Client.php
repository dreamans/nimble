<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\MongoDB;

use MongoDB\Driver\Manager;

class Client
{
    /**
     * @var MongoDB\Driver\Manager $manager
     */
    private $manager;

    /**
     * @param  string $uri
     * @param  array  $uriOptions
     * @param  aray   $driverOptions
     */
    public function __construct($uri = 'mongodb://127.0.0.1:27017', array $uriOptions = [], array $driverOptions = [])
    {
        $this->manager = new Manager($uri, $uriOptions, $driverOptions);
    }

    /**
     * @param  string $dbName
     * @param  array  $options
     *
     * @return Nimble\MongoDB\Database
     */
    public function database($dbName, array $options = [])
    {
        return new Database($this->manager, $dbName, $options);
    }
}

