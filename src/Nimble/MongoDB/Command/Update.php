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

class Update extends Command
{
    /**
     * @param  string       $databaseName
     * @param  string       $collectionName
     * @param  array|object $filter
     * @param  array|object $update
     * @param  array        $options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, $update, array $options = [])
    {
        if (!is_array($filter) && !is_object($filter)) {
            throw new InvalidArgumentException('$filter must be array or object');
        }
        if (!is_array($update) && !is_object($update)) {
            throw new InvalidArgumentException('$filter must be array or object');
        }
        $this->databaseName   = $databaseName;
        $this->collectionName = $collectionName;
        $this->filter         = $filter;
        $this->update         = $update;
        $this->options        = $options;
    }

    /**
     * @param  Server $server
     *
     * @return UpdateResult
     */
    public function execute(Server $server)
    {
        $optTypeKey = [
            'collation' => self::TYPE_OBJECT,
            'multi' => self::TYPE_BOOL,
            'upsert' => self::TYPE_BOOL,    
        ];
        $updateOpts = $this->convertOptionisType($optTypeKey);

        $bulk = new BulkWrite();
        $bulk->update($this->filter, $this->update, $updateOpts);

        $ns = $this->databaseName . '.' . $this->collectionName;
        $writeConcern = isset($this->options['writeConcern']) ? $this->options['writeConcern']: null;
        $updateResult = $server->executeBulkWrite($ns, $bulk, $writeConcern);
        return new UpdateResult($updateResult);
    }
}

