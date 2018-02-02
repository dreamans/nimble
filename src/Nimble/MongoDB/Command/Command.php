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

abstract class Command implements CommandInterface
{
    const TYPE_BOOL   = 1;
    const TYPE_ARRAY  = 2;
    const TYPE_OBJECT = 3;
    const TYPE_INT    = 4;

    /**
     * @var string
     */
    protected $databaseName;

    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var array|object
     */
    protected $filter;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array|object
     */
    protected $update;

    /**
     * @var array
     */
    protected $documents;

    /**
     * @param  array $optKeyType
     *
     * @return array $targetOpts
     */
    protected function convertOptionisType(array $optKeyType = []) {
        $targetOpts = [];
        foreach ($optKeyType as $opt => $type) {
            if (!isset($this->options[$opt])) {
                continue;
            }
            switch ($type) {
                case self::TYPE_BOOL:
                    $targetOpts[$opt] = (bool) $this->options[$opt];
                    break;
                case self::TYPE_INT:
                    $targetOpts[$opt] = (int) $this->options[$opt];
                    break;
                case self::TYPE_OBJECT:
                    $targetOpts[$opt] = (object) $this->options[$opt];
                    break;
                case self::TYPE_ARRAY:
                    $targetOpts[$opt] = (array) $this->options[$opt];
                    break;
            }
        }
        return $targetOpts;
    }
}

