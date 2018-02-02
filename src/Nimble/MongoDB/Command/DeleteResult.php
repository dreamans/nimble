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

class DeleteResult
{
    /**
     * @var MongoDB\Driver\WriteResult
     */
    private $writeResult;

    /**
     * @param  WriteResult $writeResult
     */
    public function __construct(WriteResult $writeResult)
    {
        $this->writeResult = $writeResult;
    }

    /**
     * @return int
     *
     * @throws UnacknowledgedException::writeResultAccess
     */
    public function getDeletedCount()
    {
        if (!$this->isAcknowledged()) {
            throw UnacknowledgedException::writeResultAccess(sprintf('Write unacknowledged and %s cannot be called', __METHOD__));
        }
        return $this->writeResult->getDeletedCount();
    }

    /**
     * @return bool
     */
    public function isAcknowledged()
    {
        return $this->writeResult->isAcknowledged();
    }
}

