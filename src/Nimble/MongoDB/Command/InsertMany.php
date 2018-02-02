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

class InsertMany extends Command
{
    /**
     * @param  string $databaseName
     * @param  string $collectionName
     * @param  array  $documents
     * @param  array  $options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, array $documents, array $options = [])
    {
        if (empty($documents)) {
            throw new InvalidArgumentException(sprintf('Argument $documents is empty'));
        }
        foreach ($documents as $k => $doc) {
            if (!is_array($doc) && !is_object($doc)) {
                throw new InvalidArgumentException(sprintf('$documents[%d] must be array or object', $k));
            }
        }
        $this->databaseName   = $databaseName;
        $this->collectionName = $collectionName;
        $this->documents      = $documents;
        $this->options        = $options;
    }

    /**
     * @param  Server $server
     *
     * @return InsertManyResult
     */
    public function execute(Server $server)
    {
        $bulk = new BulkWrite();
        $arrInsertedId = [];
        foreach ($this->documents as $k => $doc) {
            $arrInsertedId[$k] = $bulk->insert($doc);
        }
        $ns           = $this->databaseName . '.' . $this->collectionName;
        $writeConcern = isset($this->options['writeConcern']) ? $this->options['writeConcern']: null;
        $insertResult = $server->executeBulkWrite($ns, $bulk, $writeConcern);
        return new InsertManyResult($insertResult, $arrInsertedId);
    }
}

