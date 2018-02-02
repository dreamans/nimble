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

class Table 
{
    /**
     * @var object Client
     */
    private $client;

    /**
     * @var object Builder
     */
    private $builder;

    /**
     * @param  string $tbName
     * @param  object $client
     */
    public function __construct($client, $tbName, $tbPrefix = '')
    {
        $this->client = $client;
        $this->builder = new Builder($tbPrefix, $tbName);
    }

    /**
     * @return object
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * @return object self
     */
    public function instance()
    {
        return $this;
    }

    /**
     * @param  string|array $column
     *
     * @return $this
     */
    public function column()
    {
        $columns = func_get_args();
        if ($columns) {
            foreach ($columns as $col) {
                if (is_numeric($col)) {
                    continue;
                }
                $this->builder->addArrayColumns($col);
            }
        }
        return $this;
    }

    /**
     * @param  array $data
     *
     * @return $this
     */
    public function data(array $data)
    {
        $insData = [];
        foreach ($data as $field => $value) {
            if (is_numeric($field)) {
                continue;
            }
            $prepare = $this->makePrepare($field, $value);
            $insData[$field] = $prepare;
            $this->builder->addArrayPrepare($prepare, $value);
        }
        $this->builder->setData($insData);
        return $this;
    }

    /**
     * @param  string $column
     * @param  string $as
     *
     * @return $this
     */
    public function columnAs($column, $as, $tag = '')
    {
        $this->builder->addArrayColumns($column, $as, $tag);
        return $this;
    }

    /**
     * @param  mixed
     *
     * @return $this
     */
    public function where()
    {
        $count = func_num_args();
        $args = func_get_args();

        if ($count && is_array($args[0])) {
            foreach ($args[0] as $column => $value) {
                $prepare = $this->makePrepare($column, $value);
                $this->builder->addArrayWhere($column, '=', $prepare);
                $this->builder->addArrayPrepare($prepare, $value);
            }
        } elseif ($count >= 2 && is_string($args[0])) {
            $column = $args[0];
            $value = $args[1];
            $tag = isset($args[2]) ? $args[2] : '=';
            $prepare = $this->makePrepare($column, $value);
            $this->builder->addArrayWhere($column, $tag, $prepare);
            $this->builder->addArrayPrepare($prepare, $value);
        }
        return $this;
    }

    /**
     * @param  string $condition
     * @param  array  $prepare
     *
     * @return $this
     */
    public function whereRaw($condition, array $prepare = [])
    {
        $this->builder->addArrayWhere($condition);
        
        if ($prepare) {
            foreach ($prepare as $key => $val) {
                $this->builder->addArrayPrepare($key, $val);
            }
        }
        return $this;
    }

    /**
     * @param  string $column
     * @param  array  $arrValue
     *
     * @return $this
     */
    public function whereIn($column, array $arrValue)
    {
        if (!$arrValue) {
            return $this;
        }
        $arrPrepare = [];
        foreach ($arrValue as $key => $val) {
            $arrPrepare[] = $prepare = $this->makePrepare($column.$key, $val);
            $this->builder->addArrayPrepare($prepare, $val);
        }
        $value = '('. implode(',', $arrPrepare) .')';
        $this->builder->addArrayWhere($column, 'in', $value);
        return $this;
    }

    /**
     * @param  string $column
     * @param  string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->builder->addOrderBy($column, $direction);
        return $this;
    }

    /**
     * @param  string $column
     *
     * @return $this
     */
    public function groupBy($column)
    {
        $this->builder->addGroupBy($column);
        return $this;
    }

    /**
     * @param  int $offset
     * @param  int $limit
     *
     * @return $this
     */
    public function limit($offset = 0, $limit = 999)
    {
        $this->builder->setLimit($offset, $limit);
        return $this;
    }

    /** 
     * @return array
     */
    public function all()
    {
        $sql = $this->builder->selectSql();
        return $this->client->select($sql, $this->builder->prepares());
    }

    /**
     * @return array
     */
    public function first()
    {
        $this->limit(0, 1);
        $arrResult = $this->all();
        if (!isset($arrResult[0])) {
            return [];
        }
        return $arrResult[0];
    }

    /**
     * @return int
     */
    public function save()
    {
        $sql = $this->builder->insertSql();
        return $this->client->insert($sql, $this->builder->prepares());
    }

    /**
     * @return int
     */
    public function update()
    {
        $sql = $this->builder->updateSql();
        return $this->client->update($sql, $this->builder->prepares());
    }

    /**
     * @return int
     */
    public function delete()
    {
        $sql = $this->builder->deleteSql();
        return $this->client->update($sql, $this->builder->prepares());
    }

    /**
     * @return int
     */
    public function count()
    {
        $sql = $this->builder->countSql();
        $ret = $this->client->select($sql, $this->builder->prepares());
        if (isset($ret[0]['count'])) {
            return intval($ret[0]['count']);
        }
        return 0;
    }

    /**
     * @return int
     */
    public function lastInsertId()
    {
        return $this->client->lastInsertId();
    }

    /**
     * @param  string $key
     * @param  string $entropy
     *
     * @return string
     */
    private function makePrepare($key, $entropy)
    {
        $rawKey = md5($key. $entropy . rand());
        return ':' . $key . '_' . substr($rawKey, rand(0, 24), 8);
    }
}

