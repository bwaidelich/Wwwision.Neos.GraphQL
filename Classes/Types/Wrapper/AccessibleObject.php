<?php
namespace Wwwision\Neos\GraphQl\Types\Wrapper;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * A wrapper for arbitrary objects to expose all getters
 *
 * @Flow\Proxy(false)
 */
class AccessibleObject implements \ArrayAccess
{

    /**
     * @var mixed
     */
    protected $object;

    /**
     * @param object $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    public function offsetExists($propertyName)
    {
        if ($this->object === null) {
            return false;
        }
        return ObjectAccess::isPropertyGettable($this->object, $propertyName);
    }

    /**
     * @param string $propertyName
     * @return mixed|AccessibleObject|IterableAccessibleObject
     * @throws \TYPO3\Flow\Reflection\Exception\PropertyNotAccessibleException
     */
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

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('The AccessibleObject wrapper does not allow for mutation!', 1460895624);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException('The AccessibleObject wrapper does not allow for mutation!', 1460895625);
    }
}