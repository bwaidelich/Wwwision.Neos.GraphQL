<?php
namespace Wwwision\Neos\GraphQl\Types\Wrapper;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * A wrapper for collections of arbitrary objects to expose all getters
 *
 * @Flow\Proxy(false)
 */
class IterableAccessibleObject implements \Iterator
{

    /**
     * @var mixed
     */
    protected $innerIterator;

    /**
     * @param \Iterator|array $object
     */
    public function __construct($object)
    {
        if ($object instanceof \Iterator) {
            $this->innerIterator = $object;
        } elseif (is_array($object)) {
            $this->innerIterator = new \ArrayIterator($object);
        } else {
            throw new \InvalidArgumentException('The IterableAccessibleObject only works on arrays or objects implementing the Iterator interface', 1460895979);
        }
    }

    /**
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->innerIterator;
    }

    /**
     * @return AccessibleObject
     */
    public function current()
    {
        return new AccessibleObject($this->innerIterator->current());
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->innerIterator->next();
    }

    /**
     * @return string
     */
    public function key()
    {
        return $this->innerIterator->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->innerIterator->valid();
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->innerIterator->rewind();
    }
}