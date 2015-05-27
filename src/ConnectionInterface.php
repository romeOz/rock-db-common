<?php

namespace rock\db\common;


/**
 * Interface ConnectionInterface
 * @property \rock\cache\CacheInterface $queryCache
 * @property bool $enableQueryCache
 * @property int $queryCacheExpire
 * @property string[] $queryCacheTags
 * @property bool $autoClearCache
 * @property bool $typeCast
 */
interface ConnectionInterface 
{
    /**
     * Returns a value indicating whether the DB connection is established.
     * @return boolean whether the DB connection is established
     */
    public function getIsActive();
    /**
     * Establishes a DB connection.
     * It does nothing if a DB connection has already been established.
     *
     * @throws DbException if connection fails
     */
    public function open();

    /**
     * Closes the currently active DB connection.
     * It does nothing if the connection is already closed.
     */
    public function close();
}