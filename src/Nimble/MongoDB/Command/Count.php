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
use MongoDB\Driver\Command as MongoCommand;
use MongoDB\Driver\Exception\UnexpectedValueException;
use Nimble\MongoDB\Exception\InvalidArgumentException;

class Count extends Command
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
            throw new InvalidArgumentExceptio(nsprintf('$filter must be array or object'));
        }
        $this->databaseName   = $databaseName;
        $this->collectionName = $collectionName;
        $this->filter         = $filter;
        $this->options        = $options;
    }

    /**
     * @param  Server $server
     *
     * @return int
     *
     * @throws UnexpectedValueException
     */
    public function execute(Server $server)
    {
        $cmd = [
            'count' => $this->collectionName,
        ];
        
        if ($this->filter) {
            $cmd['query'] = (object) $this->filter;
        }

        if (isset($this->options['collation'])) {
            $cmd['collation'] = (object) $this->options['collation'];
        }

        if (isset($this->options['hint'])) {
            $cmd['hint'] = is_array($this->options['hint']) ? (object) $this->options['hint'] : $this->options['hint'];
        }

        $createCommand = new MongoCommand($cmd);

        $readPreference = isset($this->options['readPreference']) ? $this->options['readPreference']: null;
        $ns = $this->databaseName;

        $cursor = $server->executeCommand($ns, $createCommand, $readPreference);
        $result = current($cursor->toArray());
        if ( !isset($result->n) || !is_numeric($result->n)) {
            throw new UnexpectedValueException('count command did not return a numeric "n" value');
        }
        return $result->n;
    }
}

