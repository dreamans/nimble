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

interface CommandInterface
{
    /**
     * @param  MongoDB\Driver\Server $server
     * 
     * @return mixed
     */
    public function execute(Server $server);
}
