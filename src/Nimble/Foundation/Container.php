<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Foundation;

class Container
{
    /**
     * @var array
     */
    private $variables = [];

    /**
     * @param  array $vars
     */
    public function __construct(array $vars = [])
    {
        foreach($vars as $key => $val) {
            $this->variables[$key] = $val;
        }
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->variables[$key]) ? $this->variables[$key] : null;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     */
    public function __set($key, $value)
    {
        $this->variables[$key] = $value;
    }

    /**
     * @param  string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return isset($this->variables[$key]) ? true : false;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->variables;
    }

    public function flush()
    {
        $this->variables = [];
    }
}

