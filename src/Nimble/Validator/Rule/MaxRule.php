<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Validator\Rule;

class MaxRule implements RuleInterface
{
    /**
     * @param  mixed $value
     * @param  mixed $param
     *
     * @return bool
     */
    public static function rule($value, $param)
    {
        return ($value === null || $value === "") || $value <= floatval($param);
    }
}

