<?php
namespace Wwwision\Neos\GraphQl;

use GraphQL\Type\Definition\ObjectType;
use TYPO3\Flow\Annotations as Flow;

/**
 * A type resolver (aka factory) for GraphQL type definitions.
 * This class is required in order to prevent multiple instantiation of the same type and to allow types to reference themselves
 *
 * @Flow\Scope("singleton")
 */
class TypeResolver
{
    /**
     * @var ObjectType[]
     */
    private $types;

    /**
     * @param string $typeClassName
     * @return ObjectType
     */
    public function get($typeClassName)
    {
        if (!is_string($typeClassName)) {
            throw new \InvalidArgumentException(sprintf('Expected string, got "%s"', is_object($typeClassName) ? get_class($typeClassName) : gettype($typeClassName)), 1460065671);
        }
        if (!isset($this->types[$typeClassName])) {
            // forward recursive requests of the same type to a closure to prevent endless loops
            $this->types[$typeClassName] = function() use ($typeClassName) { return $this->get($typeClassName); };

            $this->types[$typeClassName] = new $typeClassName($this);
        }
        return $this->types[$typeClassName];
    }
}