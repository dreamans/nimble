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

use Pdo;
use Nimble\Mysql\Exception\QueryException;

class Client
{
    private $pdoLink = null;

    private $transaction = null;

    private $config = [];

    private $lastQuerySql = [];

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->pdoLink = $this->connect();
    }

    /**
     * @param  string $tbName
     */
    public function table($tbName, $tbPrefix = '')
    {
        return new Table($this, $tbName, $tbPrefix);
    }

    /**
     * @return array
     */
    public function lastQuerySql()
    {
        return $this->lastQuerySql;
    }

    /**
     * @return string
     */
    public function lastQuerySqlString()
    {
        return json_encode($this->lastQuerySql);
    }

    /**
     * @return bool
     *
     * @throws QueryException::transaction
     */
    public function beginTransaction()
    {
        if ($this->transaction) {
            throw QueryException::transaction("translation is active and cannot open new translation operations");
        }
        return $this->transaction = $this->pdoLink->beginTransaction();
    }

    /**
     * @return bool
     *
     * @throws QueryException::transaction
     */
    public function commit()
    {
        if (!$this->transaction) {
            throw QueryException::transaction("translation is inactive and cannot perform commit operations");
        }
        $this->transaction = false;
        return $this->pdoLink->commit();
    }

    /**
     * @return bool
     *
     * @throws QueryException::transaction
     */
    public function rollBack()
    {
        if (!$this->transaction) {
            throw QueryException::transaction("translation is inactive and cannot perform roll back operations");
        }
        $this->transaction = false;
        return $this->pdoLink->rollBack();
    }

    /**
     * @param  string $sql
     * @param  array  $prepare
     *
     * @return array
     */
    public function select($sql, array $prepare = [])
    {
        $pdoSh = $this->query($sql, $prepare);
        return $pdoSh->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param  string $sql
     * @param  array  $prepare
     *
     * @return int
     */
    public function insert($sql, array $prepare = [])
    {
        $this->query($sql, $prepare);
        return $this->pdoLink->lastInsertId();
    }

    /**
     * @param  string $sql
     * @param  array $prepare
     *
     * @return int
     */
    public function update($sql, array $prepare = [])
    {
        $pdoSh = $this->query($sql, $prepare);
        return $pdoSh->rowCount();
    }

    /**
     * @return int
     */
    public function lastInsertId()
    {
        return $this->pdoLink->lastInsertId();
    }

    /**
     * @param  string $sql
     * @param  array  $prepare
     *
     * @return PDOStatement
     *
     * @throws QueryException
     */
    public function query($sql, array $prepare = [])
    {
        $pdoSth = $this->pdoLink->prepare($sql);
        $pdoSth->execute($prepare);
        $this->lastQuerySql = [$pdoSth->queryString, $prepare];
        $errorInfo = $pdoSth->errorInfo();
        if ($errorInfo[0] != '00000') {
            throw QueryException::executeSql($errorInfo, $sql);
        }
        return $pdoSth;
    }

    /**
     * @return new Pdo
     *
     * @throws PDOException
     */
    private function connect()
    {
        $dsn = $this->getDsn();
        $user = $this->getUser();
        $pass = $this->getPass();
        $options = $this->getOptions();

        return new Pdo($dsn, $user, $pass, $options);
    }

    /**
     * @return string
     */
    private function getDsn()
    {
        $dsn = [];
        if (isset($this->config['db'])) {
            $dsn[] = "dbname={$this->config['db']}";
        }
        if (isset($this->config['host'])) {
            $dsn[] = "host={$this->config['host']}";
        }
        if (isset($this->config['port'])) {
            $dsn[] = "port={$this->config['port']}";
        }
        if (isset($this->config['charset'])) {
            $dsn[] = "charset={$this->config['charset']}";
        }
        $strDsn = "mysql:" . implode(';', $dsn);

        return $strDsn;
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        return isset($this->config['options']) ? $this->config['options'] : [];
    }

    /**
     * @return string|null
     */
    private function getUser()
    {
        return isset($this->config['user']) ? $this->config['user'] : null;
    }
    
    /**
     * @return string|null
     */
    private function getPass()
    {
        return isset($this->config['pass']) ? $this->config['pass'] : null;
    }
}

