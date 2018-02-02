<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\MongoDB\Command;

use MongoDB\Driver\Server;
use MongoDB\Driver\Command as DriverCommand;
use MongoDB\Driver\Exception\RuntimeException;
use Nimble\MongoDB\Exception\InvalidArgumentException;

class DropCollection extends Command
{
    /**
     * @param  string $databaseName
     * @param  string $collectionName
     * @param  array  $options
     */
    public function __construct($databaseName, $collectionName, array $options = [])
    {
        $this->databaseName   = $databaseName;
        $this->collectionName = $collectionName;
        $this->options        = $options;
    }

    /**
     * @param  Server $server
     *
     * @return array
     *
     * @throws RuntimeException
     */
    public function execute(Server $server)
    {
        $exeCommand = new DriverCommand([
            'drop' => $this->collectionName,
        ]);

        try {
            $cursor = $server->executeCommand($this->databaseName, $exeCommand);
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'ns not found') {
                return (object) ['ns' => $this->databaseName . '.' . $this->collectionName, 'ok' => 0, 'errmsg' => $e->getMessage()];
            }
        }
        if (isset($this->options['typeMap'])) {
            $cursor->setTypeMap($this->options['typeMap']);
        }
        return current($cursor->toArray());
    }
}

