<?php
namespace Wwwision\Neos\GraphQl\Types\Wrapper;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * @Flow\Proxy(false)
 */
class IterableAccessibleObject implements \Iterator
{

    /**
     * @var mixed
     */
    protected $innerIterator;

    public function __construct($object)
    {
        $this->innerIterator = $object instanceof \Iterator ? $object : new \ArrayIterator($object);
    }

    /**
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->innerIterator;
    }

    public function current()
    {
        return new AccessibleObject($this->innerIterator->current());
    }

    public function next()
    {
        $this->innerIterator->next();
    }

    public function key()
    {
        return $this->innerIterator->key();
    }

    public function valid()
    {
        return $this->innerIterator->valid();
    }

    public function rewind()
    {
        $this->innerIterator->rewind();
    }
}