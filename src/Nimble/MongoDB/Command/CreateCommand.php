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

use Nimble\MongoDB\Exception\InvalidCommandException;

class CreateCommand 
{
    /**
     * @var $cmd
     */
    private $cmd;

    /**
     * @var cmd alias
     */
    private $alias = [
        'insert' => 'InsertOne',
    ];
    
    /**
     * @param  string $databaseName
     * @param  string $cmd
     * @param  array  $options
     */
    public function __construct($databaseName, $cmd, array $options)
    {
        if (!isset($this->alias[$cmd])) {
            throw new InvalidCommandException(sprintf("MongoDB command '%s' invalid"));
        }

        $cmdObject = "Nimble\\MongoDB\\Command\\{$this->alias[$cmd]}";
        $this->cmd = new $cmdObject($databaseName, $options);
    }

    public function execute()
    {
        return $this->cmd->execute();
    }
}

