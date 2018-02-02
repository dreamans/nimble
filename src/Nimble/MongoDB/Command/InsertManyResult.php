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

use MongoDB\Driver\WriteResult;
use Nimble\MongoDB\Exception\UnacknowledgedException;

class InsertManyResult
{
    /**
     * @var MongoDB\Driver\WriteResult
     */
    private $writeResult;

    /**
     * @var array
     */
    private $arrInsertedId;

    /**
     * @param  WriteResult $writeResult
     * @param  mixed       $insertedId
     */
    public function __construct(WriteResult $writeResult, $arrInsertedId)
    {
        $this->writeResult   = $writeResult;
        $this->arrInsertedId = $arrInsertedId;
    }

    /**
     * @return bool
     */
    public function isAcknowledged()
    {
        return $this->writeResult->isAcknowledged();
    }

    /**
     * @return mixed
     */
    public function getInsertedId()
    {
        return $this->arrInsertedId;
    }

    /**
     * @return int|null
     *
     * @throws UnacknowledgedException::writeResultAccess
     */
    public function getInsertedCount()
    {
        if (!$this->isAcknowledged()) {
            throw UnacknowledgedException::writeResultAccess(sprintf('Write unacknowledged and %s cannot be called', __METHOD__));
        }
        return $this->writeResult->getInsertedCount();
    }
}

