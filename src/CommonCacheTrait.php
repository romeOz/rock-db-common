<?php

namespace rock\db\common;

trait CommonCacheTrait
{
    protected $cacheExpire;
    protected $cacheTags;
    protected $enableCache;

    /**
     * Turns on query caching.
     * This method is provided as a shortcut to setting two properties that are related
     * with query caching: {@see \rock\db\Connection::$queryCacheExpire} and {@see \rock\db\Connection::$queryCacheTags}.
     *
     * @param int|null $expire
     * @param string[] $tags the dependency for the cached query result.
     * See {@see \rock\db\Connection::$queryCacheTags} for more details.
     * If not set, it will use the value of {@see \rock\db\Connection::$queryCacheExpire}. See {@see \rock\db\Connection::$queryCacheExpire} for more details.
     * @return $this
     */
    public function cache($expire = null, array $tags = [])
    {
        $this->enableCache = true;
        $this->cacheExpire = $expire;
        $this->cacheTags = $tags;
        return $this;
    }

    /**
     * Turns off query caching.
     */
    public function notCache()
    {
        $this->enableCache = false;
        $this->cacheExpire = $this->cacheTags = null;
        return $this;
    }

    /**
     * @param ConnectionInterface $connection
     * @return mixed
     */
    protected function calculateCacheParams(ConnectionInterface $connection)
    {
        if (isset($this->enableCache)) {
            $connection->enableQueryCache = $this->enableCache;
        }
        if (isset($this->cacheExpire)) {
            $connection->queryCacheExpire = $this->cacheExpire;
        }
        if (isset($this->cacheTags)) {
            $connection->queryCacheTags = $this->cacheTags;
        }
        return $connection;
    }
}