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
use MongoDB\Driver\ReadPreference;
use Nimble\MongoDB\Command\CreateCommand;

class Database
{
    /**
     * @var MongoDB\Driver\Manager $manager
     */
    private $manager;

    /**
     * @var string $databaseName
     */
    private $databaseName;

    /**
     * @var MongoDB\Driver\ReadPreference $readConcern
     */
    private $readConcern;

    /**
     * @var MongoDB\Driver\WriteConcern $writeConcern
     */
    private $writeConcern;

    /**
     * @var MongoDB\Driver\ReadPreference $readPreference
     */
    private $readPreference;

    /**
     * @param  MongoDB\Driver\Manager $manager
     * @param  string                 $dbName
     * @param  array                  $options
     */
    public function __construct(Manager $manager, $dbName, $options)
    {
        $this->manager        = $manager;
        $this->databaseName   = $dbName;
        $this->readConcern    = isset($options['readConcern'])  ? $options['readConcern']  : $manager->getReadConcern();
        $this->writeConcern   = isset($options['writeConcern']) ? $options['writeConcern'] : $manager->getWriteConcern();
        $this->readPreference = isset($options['readPreference']) ? $options['readPreference'] : $manager->getReadPreference();
    }

    /**
     * @param  string $cmd
     * @param  array  $options
     *
     * @return new CreateCommand 
     */
    public function command($cmd, array $options = [])
    {
        return new CreateCommand($this->databaseName, $cmd, $options);
    }

    /**
     * @param  string $collectionName
     *
     * @return new Collection
     */
    public function collection($collectionName)
    {
        $options = [
            'readConcern'    => $this->readConcern,
            'writeConcern'   => $this->writeConcern,
            'readPreference' => $this->readPreference,
        ];
        return new Collection($this->manager, $this->databaseName, $collectionName, $options);
    }

    /**
     * @param  string $collectionName
     */
    public function __get($collectionName)
    {
        return $this->collection($collectionName);
    }
}

