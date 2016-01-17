<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

use Traversable;
use ArrayIterator;
use Zend\Filter\FilterInterface;
use Zend\Paginator\Adapter;

class Paginator extends GlobalPaginator
{
    use JsonSerializeTrait,
        RenderTrait,
        FilterTrait,
        CacheTrait;

    /**
     * @inheritdoc
     */
    public function getItemsByPage($pageNumber)
    {
        $pageNumber = $this->normalizePageNumber($pageNumber);

        $adapter = $this->adapter;

        $filter = $this->getFilter();
        if ($filter instanceof FilterInterface) {
            $adapter = new Adapter\FilterAdapter($adapter, $filter);
        }

        if ($this->cacheEnabled()) {
            $adapter = new Adapter\CacheAdapter($adapter, $this->cache);
        }

        $offset = ($pageNumber - 1) * $this->getItemCountPerPage();

        $items =  $adapter->getItems($offset, $this->getItemCountPerPage());

        if (!$items instanceof Traversable) {
            $items = new ArrayIterator($items);
        }

        return $items;
    }
}
