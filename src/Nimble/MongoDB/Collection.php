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
use Nimble\MongoDB\Command\InsertOne;
use Nimble\MongoDB\Command\InsertMany;
use Nimble\MongoDB\Command\Find;
use Nimble\MongoDB\Command\DropCollection;
use Nimble\MongoDB\Command\Update;
use Nimble\MongoDB\Command\Delete;
use Nimble\MongoDB\Command\Count;

class Collection
{
    /**
     * @var array
     */
    private $defaultTypeMap = [
        'array'    => 'array',
        'document' => 'array',
        'root'     => 'array',
    ];
    /**
     * @var MongoDB\Driver\Manager $manager
     */
    private $manager;

    /**
     * @var string $databaseName
     */
    private $databaseName;

    /**
     * @var string $collectionName
     */
    private $collectionName;

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
     * @param  string                 $databaseName
     * @param  string                 $collectionName
     * @param  array                  $options
     */
    public function __construct(Manager $manager, $databaseName, $collectionName, array $options = [])
    {
        $this->manager        = $manager;
        $this->databaseName   = $databaseName;
        $this->collectionName = $collectionName;
        $this->readConcern    = $options['readConcern'];
        $this->writeConcern   = $options['writeConcern'];
        $this->readPreference = $options['readPreference'];
    }

    /**
     * @param  string $document
     * @param  array  $options
     *
     * @return object InsertOneResult
     */
    public function insertOne($document, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern'] = $this->writeConcern;
        }
        $cmd = new InsertOne($this->databaseName, $this->collectionName, $document, $options);
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $cmd->execute($server);
    }

    /**
     * @param  array  $documents
     * @param  array  $options
     * 
     * @return object InsertManyResult
     */
    public function insertMany(array $documents, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern'] = $this->writeConcern;
        }
        $cmd = new InsertMany($this->databaseName, $this->collectionName, $documents, $options);
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $cmd->execute($server);
    }

    /**
     * @param  array|object $filter
     * @param  array        $options
     *
     * @return MongoDB\Driver\Cursor
     */
    public function find($filter = [], array $options = [])
    {
        if (!isset($options['readPreference'])) {
            $options['readPreference'] = $this->readPreference;
        }
        if (!isset($options['typeMap'])) {
            $options['typeMap'] = $this->defaultTypeMap;
        }
        $server = $this->manager->selectServer($options['readPreference']);
        $cmd = new Find($this->databaseName, $this->collectionName, $filter, $options);

        return $cmd->execute($server);
    }

    /**
     * @param  array|object $filter
     * @param  array        $options
     *
     * @return array|null
     */
    public function findOne($filter = [], array $options = [])
    {
        $options['limit'] = 1;
        $cursor = $this->find($filter, $options);
        $document = current($cursor->toArray());

        return false !== $document ? $document : null;
    }

    /**
     * @param  array|object $filter
     * @param  array|object $update
     * @param  array        $options
     *
     * @return UpdateResult
     */
    public function update($filter, $update, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern'] = $this->writeConcern;
        }
        $cmd = new Update($this->databaseName, $this->collectionName, $filter, $update, $options);
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $cmd->execute($server);
    }

    /**
     * @param  array|object $filter
     * @param  array|object $update
     * @param  array        $options
     *
     * @return UpdateResult
     */
    public function updateOne($filter, $update, array $options = [])
    {
        $options['multi'] = false;
        return $this->update($filter, $update, $options);
    }

    /**
     * @param  array|object $filter
     * @param  array        $options
     *
     * @return DeleteResult
     */
    public function delete($filter, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern'] = $this->writeConcern;
        }
        $cmd = new Delete($this->databaseName, $this->collectionName, $filter, $options);
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $cmd->execute($server);
    }

    /**
     * @param  array|object $filter
     * @param  array        $options
     *
     * @return DeleteResult
     */
    public function deleteOne($filter, array $options = [])
    {
        $options['limit'] = true;
        return $this->delete($filter, $options);
    }

    /**
     * @param  array|object $filter
     * @param  array        $options
     *
     * @return DeleteResult
     */
    public function deleteAll($filter, array $options = [])
    {
        $options['limit'] = false;
        return $this->delete($filter, $options);
    }

    /**
     * @param  array $options
     *
     * @return array
     */
    public function drop(array $options = [])
    {
        if (!isset($options['typeMap'])) {
            $options['typeMap'] = $this->defaultTypeMap;
        }
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));
        $cmd = new DropCollection($this->databaseName, $this->collectionName, $options);

        return $cmd->execute($server);
    }

    public function count($filter = [], array $options = [])
    {
        if (!isset($options['readPreference'])) {
            $options['readPreference'] = $this->readPreference;
        }
        if (!isset($options['typeMap'])) {
            $options['typeMap'] = $this->defaultTypeMap;
        }
        $server = $this->manager->selectServer($options['readPreference']);
        $cmd = new Count($this->databaseName, $this->collectionName, $filter, $options);

        return $cmd->execute($server);
    }
}

