<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

use Zend\Cache\Storage\IteratorInterface as CacheIterator;
use Zend\Cache\Storage\StorageInterface as CacheStorage;

use Zend\Paginator\Adapter\CacheAdapter;

trait CacheTrait
{

    /**
     * Enable or disable the cache by Zend\Paginator\Paginator instance
     *
     * @var bool
     */
    protected $cacheEnabled = true;

    /**
     * Cache object
     *
     * @var CacheStorage
     */
    protected $cache;

    /**
     * Sets a cache object
     *
     * @param CacheStorage $cache
     */
    public function setCache(CacheStorage $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Enables/Disables the cache for this instance
     *
     * @param bool $enable
     * @return Paginator
     */
    public function setCacheEnabled($enable)
    {
        $this->cacheEnabled = (bool) $enable;
        return $this;
    }

    /**
     * Tells if there is an active cache object
     * and if the cache has not been disabled
     *
     * @return bool
     */
    protected function cacheEnabled()
    {
        return (($this->cache instanceof CacheStorage) && $this->cacheEnabled);
    }

    /**
     * Returns the page item cache.
     *
     * @return array
     */
    public function getPageItemCache()
    {
        $data = [];
        if ($this->cacheEnabled()) {
            $prefixLength  = strlen(CacheAdapter::CACHE_TAG_PREFIX);
            $cacheIterator = $this->cache->getIterator();
            $cacheIterator->setMode(CacheIterator::CURRENT_AS_VALUE);
            foreach ($cacheIterator as $key => $value) {
                if (substr($key, 0, $prefixLength) == CacheAdapter::CACHE_TAG_PREFIX) {
                    $pageNumber = (int)substr($key, $prefixLength);
                    $data[$pageNumber] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Clear the page item cache.
     *
     * @param int $pageNumber
     * @return Paginator
     */
    public function clearPageItemCache($pageNumber = null)
    {
        if (!$this->cacheEnabled()) {
            return $this;
        }

        $cacheAdapter = new CacheAdapter($this->adapter, $this->cache);

        if (null === $pageNumber) {
            $prefixLength  = strlen(CacheAdapter::CACHE_TAG_PREFIX);
            $cacheIterator = $this->cache->getIterator();
            $cacheIterator->setMode(CacheIterator::CURRENT_AS_KEY);
            foreach ($cacheIterator as $key) {
                if (substr($key, 0, $prefixLength) == CacheAdapter::CACHE_TAG_PREFIX) {
                    $pageNumber = (int)substr($key, $prefixLength);
                    $cacheAdapter->clearCache($pageNumber, $this->getItemCountPerPage());
                }
            }
        } else {
            $pageNumber = $this->normalizePageNumber($pageNumber);

            $cacheAdapter->clearCache($pageNumber, $this->getItemCountPerPage());
        }
        return $this;
    }
}