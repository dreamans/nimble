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

class Builder 
{
    const LIMIT_DEFAULT = '0, 999';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    private $wheres = [];

    /**
     * @var array
     */
    private $prepares = [];

    /**
     * @var array
     */
    private $columns = [];

    /**
     * @var array
     */
    private $orders = [];

    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var string
     */
    private $limit;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param  string $prefix
     * @param  string $tbName
     */
    public function __construct($prefix = '', $tbName = '')
    {
        $this->prefix = $prefix;
        $this->table = $tbName;
    }

    /**
     * @return string
     */
    public function selectSql()
    {
        $where = $this->toWheres();
        $where = $where ? ' AND ' . $where: ' ';
        $sql = sprintf("SELECT %s FROM %s WHERE 1%s%s%s LIMIT %s", 
            $this->toColumns(), $this->toTable(), $where, 
            $this->toGroups(), $this->toOrders(), $this->toLimit()
        );
        return $sql;
    }

    /**
     * @return string
     */
    public function countSql()
    {
        $where = $this->toWheres();
        $where = $where ? ' AND ' . $where: '';
        $sql = sprintf("SELECT COUNT(1) as count FROM %s WHERE 1%s", 
            $this->toTable(), $where
        );
        return $sql;
    }

    /**
     * @reutrn string
     */
    public function insertSql()
    {
        list($fields, $values) = $this->toData();
        $sql = sprintf("INSERT INTO %s(%s) VALUES(%s)",
            $this->toTable(), $fields, $values
        );
        return $sql;
    }

    /**
     * @return string
     */
    public function updateSql()
    {
        $sql = sprintf("UPDATE %s SET %s WHERE %s",
            $this->toTable(), $this->toUpdate(), $this->toWheres()
        );
        return $sql;
    }

    /**
     * @return string
     */
    public function deleteSql()
    {
        $sql = sprintf("DELETE FROM %s WHERE %s",
            $this->toTable(), $this->toWheres()
        );
        return $sql;
    }

    /**
     * @return array
     */
    public function prepares()
    {
        return $this->prepares;
    }

    /**
     * @param  string $tbName
     * @param  string|null $tbPrefix
     */
    public function setTable($tbName, $tbPrefix = null)
    {
        $this->table = $tbName;
        if (null !== $tbPrefix) {
            $this->prefix = $tbPrefix;
        }
    }

    /**
     * @param  int $offset
     * @param  int $limit
     */
    public function setLimit($offset = 0, $limit = 999)
    {
        $this->limit = sprintf("%d, %d", intval($offset), intval($limit));
    }

    /**
     * @param  array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param  string $column
     * @param  string $direction
     */
    public function addOrderBy($column, $direction = 'asc')
    {
        $this->orders[] = [$column, $direction];
    }

    /**
     * @param  string $column
     */
    public function addGroupBy($column)
    {
        $this->groups[] = $column;
    }

    /**
     * @param  string $column
     * @param  string|null $tag
     * @param  string|null $value
     */
    public function addArrayWhere($column, $tag = null, $value = null)
    {
        $this->wheres[] = [$column, $tag, $value];
    }

    /**
     * @param  string $key
     * @param  string $value
     */
    public function addArrayPrepare($key, $value)
    {
        $this->prepares[$key] = $value;
    }

    /**
     * @param  string $colums
     * @param  string $as
     * @param  string $tag
     */
    public function addArrayColumns($colums, $as = '', $tag = '`')
    {
        $this->columns[] = [$colums, $as, $tag];
    }

    /**
     * @return string
     */
    private function toOrders()
    {
        $order = "";
        if ($this->orders) {
            $arrOrder = [];
            foreach ($this->orders as $odr) {
                list($col, $direction) = $odr;
                $col = trim($col);
                $direction = strtoupper($direction);
                $arrOrder[] = "`{$col}` {$direction}";
            }
            $order = " ORDER BY " . implode(', ', $arrOrder);
        }
        return $order;
    }

    /**
     * @return string
     */
    private function toGroups()
    {
        $group = "";
        if ($this->groups) {
            $arrGroup = [];
            foreach ($this->groups as $gop) {
                $gop = trim($gop);
                $arrGroup[] = "`{$gop}`";
            }
            $group = " GROUP BY ". implode(',', $arrGroup);
        }
        return $group;
    }

    /**
     * @return string $tableName
     */
    private function toTable()
    {
        return "`{$this->prefix}{$this->table}`";
    }

    /**
     * @return string $columns
     */
    private function toColumns()
    {
        $columns = "*";
        if ($this->columns) {
            $arrCols = [];
            foreach ($this->columns as $column) {
                list($col, $as, $tag) = $column;
                $col = trim($col);
                $as  = trim($as);
                if ($as) {
                    $arrCols[] = "{$tag}".trim($col)."{$tag} as {$tag}{$as}{$tag}";
                } else {
                    $arrCols[] = "{$tag}".trim($col)."{$tag}";
                }
            }
            $columns = implode(', ', $arrCols);
        }
        return $columns;
    }

    /**
     * @return string $where
     */
    private function toWheres()
    {
        $arrWhere = [];
        if ($this->wheres) {
            foreach ($this->wheres as $w) {
                list($col, $bool, $val) = $w;
                if ($bool) {
                    $arrWhere[] = "{$col} {$bool} {$val}";
                } else {
                    $arrWhere[] = $col;
                }
            }
        }
        $where = implode (' AND ', $arrWhere);
        return $where;
    }

    /**
     * @return array
     */
    private function toData()
    {
        $insData = $this->data;
        $fields = $values = [];
        foreach ($insData as $key => $val) {
            $fields[] = "`{$key}`";
            $values[] = $val;
        }
        return [implode(", ", $fields), implode(", ", $values)];
    }

    /**
     * @return string
     */
    private function toLimit()
    {
        $limit = $this->limit;
        if (!$limit) {
            $limit = self::LIMIT_DEFAULT;
        }
        return $limit;
    }

    /**
     * @return string
     */
    private function toUpdate()
    {
        $update = [];
        $upData = $this->data;
        foreach ($upData as $field => $val) {
            $update[] = sprintf("`%s` = %s", $field, $val);
        }
        return implode(", ", $update);
    }
}

