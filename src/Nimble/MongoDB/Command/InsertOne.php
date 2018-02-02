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
use MongoDB\Driver\BulkWrite;
use Nimble\MongoDB\Exception\InvalidArgumentException;

class InsertOne extends Command
{
    /**
     * @param  string        $databaseName
     * @param  string        $collectionName
     * @param  array|object  $document
     * @param  array         $options
     */
    public function __construct($databaseName, $collectionName, $document, array $options = [])
    {
        if (empty($document)) {
            throw new InvalidArgumentException(sprintf('$document is empty'));
        }
        if (!is_array($document) && !is_object($document)) {
            throw new InvalidArgumentException(sprintf('$document must be array or object'));
        }
        $this->databaseName   = $databaseName;
        $this->collectionName = $collectionName;
        $this->document       = $document;
        $this->options        = $options;
    }

    /**
     * @param  Server $server
     *
     * @return InsertOneResult
     */
    public function execute(Server $server)
    {
        $bulk     = new BulkWrite();
        $insertedId = $bulk->insert($this->document);

        $ns           = $this->databaseName . '.' . $this->collectionName;
        $writeConcern = isset($this->options['writeConcern']) ? $this->options['writeConcern']: null;
        $insertResult = $server->executeBulkWrite($ns, $bulk, $writeConcern);
        return new InsertOneResult($insertResult, $insertedId);
    }
}

