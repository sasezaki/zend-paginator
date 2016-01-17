<?php
namespace Zend\Paginator\Adapter;

use Zend\Filter\FilterInterface;

class FilterAdapter implements AdapterInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var FilterInterface;
     */
    private $filter;

    public function __construct(AdapterInterface $adapter, FilterInterface $filter)
    {
        $this->adapter = $adapter;
        $this->filter = $filter;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        $items = $this->adapter->getItems($offset, $itemCountPerPage);
        return $this->filter->filter($items);
    }

    public function count()
    {
        return $this->adapter->count();
    }
}