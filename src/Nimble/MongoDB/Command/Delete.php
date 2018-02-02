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

class Delete extends Command
{
    /**
     * @param  string        $databaseName
     * @param  string        $collectionName
     * @param  array|object  $document
     * @param  array         $options
     */
    public function __construct($databaseName, $collectionName, $filter, array $options = [])
    {
        if (!is_array($filter) && !is_object($filter)) {
            throw new InvalidArgumentException('$filter must be array or object');
        }
        $this->databaseName   = $databaseName;
        $this->collectionName = $collectionName;
        $this->filter         = $filter;
        $this->options        = $options;
    }

    /**
     * @param  Server $server
     *
     * @return DeleteResult
     */
    public function execute(Server $server)
    {
        $optTypeKey = [
            'limit' => self::TYPE_BOOL,
            'collation' => self::TYPE_OBJECT,    
        ];
        $deleteOpts = $this->convertOptionisType($optTypeKey);
        $bulk = new BulkWrite();
        $bulk->delete($this->filter, $deleteOpts);

        $ns = $this->databaseName . '.' . $this->collectionName;
        $writeConcern = isset($this->options['writeConcern']) ? $this->options['writeConcern']: null;
        $deleteResult = $server->executeBulkWrite($ns, $bulk, $writeConcern);
        return new DeleteResult($deleteResult);
    }
}

