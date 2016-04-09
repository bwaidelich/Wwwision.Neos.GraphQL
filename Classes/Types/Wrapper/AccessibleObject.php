<?php
namespace Wwwision\Neos\GraphQl\Types\Wrapper;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * @Flow\Proxy(false)
 */
class AccessibleObject implements \ArrayAccess
{

    /**
     * @var mixed
     */
    protected $object;

    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    public function offsetExists($propertyName)
    {
        if ($this->object === null) {
            return false;
        }
        return ObjectAccess::isPropertyGettable($this->object, $propertyName);
    }

    public function offsetGet($propertyName)
    {
        if ($this->object === null) {
            return null;
        }
        $result = ObjectAccess::getProperty($this->object, $propertyName);
        if ($result instanceof \Iterator) {
            return new IterableAccessibleObject($result);
        }
        if ($result instanceof \DateTimeInterface) {
            return $result;
        }
        if (is_object($result)) {
            return new self($result);
        }
        return $result;
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
}