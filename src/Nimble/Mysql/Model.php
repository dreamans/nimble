<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Mysql;

class Model
{
    /**
     * @var array ClientPool
     */
    private static $clientPool = [];

    /**
     * @var string
     */
    protected $tbPrefix;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var object Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $clientName;

    /**
     * @var array
     */
    protected $insertPair = [];

    public function __construct()
    {
        if (!$this->clientName) {
            $config = $this->getClientConfig();
            $this->clientName = substr(md5(implode(',', $config)), 0, 8);
        }

        if (!isset(self::$clientPool[$this->clientName])) {
            $config = $this->getClientConfig();
            self::$clientPool[$this->clientName] = new Client($config);
        }

        $this->client = self::$clientPool[$this->clientName];
    }

    /**
     * @return int
     */
    public function save()
    {
        $model = $this->data($this->insertPair);
        return $model->save();
    }

    /**
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static;
        return call_user_func_array([$instance, $method], $parameters);
    }

    /**
     * @param  string $menthod
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $query = new Table(
            $this->client, 
            $this->table, 
            $this->tbPrefix
        );
        return call_user_func_array([$query, $method], $parameters);
    }

    /**
     * @param  string $key
     * @param  string $key
     */
    public function __set($key, $val)
    {
        $this->insertPair[$key] = $val;
    }

    /**
     * @return array
     */
    private function getClientConfig()
    {
        $cfg = [];
        foreach (['host', 'port', 'user', 'pass', 'db', 'charset'] as $key) {
            $cfg[$key] = isset($this->config[$key]) ? $this->config[$key] : null;
        }
        return $cfg;
    }
}

