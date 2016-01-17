<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator\Adapter;

use Zend\Cache\Storage\StorageInterface as CacheStorage;

class CacheAdapter implements AdapterInterface
{
    /**
     * The cache tag prefix used to namespace Paginator results in the cache
     *
     */
    const CACHE_TAG_PREFIX = 'Zend_Paginator_';

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var CacheStorage;
     */
    private $cache;

    public function __construct(AdapterInterface $adapter, CacheStorage $cache)
    {
        $this->adapter = $adapter;
        $this->cache = $cache;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        $pageNumber =  ($offset === 0 ) ? 1 : $offset / $itemCountPerPage + 1;

        $cacheId = $this->getCacheId($pageNumber, $itemCountPerPage);

        if ($this->cache->hasItem($cacheId)) {
            return $this->cache->getItem($cacheId);
        }

        $items = $this->adapter->getItems($offset, $itemCountPerPage);

        if (!$items instanceof \Traversable) {
            $items = new \ArrayIterator($items);
        }

        $this->cache->setItem($cacheId, $items);

        return $items;
    }

    public function count()
    {
        return $this->adapter->count();
    }

    public function clearCache($pageNumber, $itemCountPerPage)
    {
        $cacheId = $this->getCacheId($pageNumber, $itemCountPerPage);

        if ($this->cache->hasItem($cacheId)) {
            return $this->cache->removeItem($cacheId);
        }
    }

    protected function getCacheId($pageNumber, $itemCountPerPage)
    {
        return static::CACHE_TAG_PREFIX . $pageNumber . '_' . $this->getCacheInternalId($itemCountPerPage);
    }

    /**
     * Get the internal cache id
     * Depends on the adapter and the item count per page
     *
     * Used to tag that unique Paginator instance in cache
     *
     * @return string
     */
    protected function getCacheInternalId($itemCountPerPage)
    {
        return md5(serialize([
            spl_object_hash($this->adapter),
            $itemCountPerPage
        ]));
    }
}