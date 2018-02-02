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
use MongoDB\Driver\Query;
use Nimble\MongoDB\Exception\InvalidArgumentException;

class Find extends Command
{

    /**
     * QueryOptions description:
     *
     *  *  $options['allowPartialResults'] - bool
     *     For queries against a sharded collection, returns partial results from the mongos 
     *     if some shards are unavailable instead of throwing an error.
     *
     *     Falls back to the deprecated "partial" option if not specified.
     *
     *  *  $options['']
     *
     * @param  string       $databaseName
     * @param  string       $collectionName
     * @param  array|object $filter
     * @param  array        $options
     */
    public function __construct($databaseName, $collectionName, $filter, array $options = [])
    {
        if (!is_array($filter) && !is_object($filter)) {
            throw new InvalidArgumentException(sprintf('$filter must be array or object'));
        }
        $this->databaseName   = $databaseName;
        $this->collectionName = $collectionName;
        $this->filter         = $filter;
        $this->options        = $options;
    }

    /**
     * @param  Server $server
     *
     * @return MongoDB\Driver\Cursor
     */
    public function execute(Server $server)
    {
        $findOpts = $this->createQueryOptions();
        $query = new Query($this->filter, $findOpts);
        $readPreference = isset($this->options['readPreference']) ? $this->options['readPreference']: null;
        $ns = $this->databaseName . '.' . $this->collectionName;

        $cursor = $server->executeQuery($ns, $query, $readPreference);
        if (isset($this->options['typeMap'])) {
            $cursor->setTypeMap($this->options['typeMap']);
        }
        return $cursor;
    }

    /**
     * @return array queryOptions
     */
    private function createQueryOptions()
    {
        $optTypeKey = [
            'allowPartialResults' => self::TYPE_BOOL,
            'awaitData' => self::TYPE_BOOL, 
            'exhaust' => self::TYPE_BOOL, 
            'noCursorTimeout' => self::TYPE_BOOL, 
            'oplogReplay' => self::TYPE_BOOL, 
            'returnKey' => self::TYPE_BOOL, 
            'showRecordId' => self::TYPE_BOOL, 
            'singleBatch' => self::TYPE_BOOL, 
            'slaveOk' => self::TYPE_BOOL, 
            'snapshot' => self::TYPE_BOOL, 
            'tailable' => self::TYPE_BOOL,
            'batchSize' => self::TYPE_INT, 
            'limit' => self::TYPE_INT, 
            'maxScan' => self::TYPE_INT, 
            'maxAwaitTimeMS' => self::TYPE_INT, 
            'maxTimeMS' => self::TYPE_INT, 
            'skip' => self::TYPE_INT,
            'collation' => self::TYPE_OBJECT, 
            'max' => self::TYPE_OBJECT, 
            'min' => self::TYPE_OBJECT,
            'modifiers' => self::TYPE_ARRAY,
            'sort' => self::TYPE_OBJECT,
            'projection' => self::TYPE_OBJECT,
        ];

        return $this->convertOptionisType($optTypeKey);
    }
}

