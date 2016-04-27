<?php
namespace Wwwision\Neos\GraphQL\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;
use Wwwision\GraphQL\IterableAccessibleObject;

/**
 * Type scalar for unknown structures (represented as JSON object)
 */
class UnstructuredObjectScalar extends ScalarType
{
    /**
     * @var string
     */
    public $name = 'UnstructuredObjectScalar';

    /**
     * @var string
     */
    public $description = 'Type scalar for unknown structures (represented as JSON object)';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array $value
     * @return array
     */
    public function serialize($value)
    {
        if ($value instanceof IterableAccessibleObject) {
            return iterator_to_array($value->getIterator());
        }
        if (!is_array($value)) {
            return null;
        }
        return $value;
    }

    /**
     * @param string|array $value
     * @return null
     */
    public function parseValue($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (is_array($value)) {
            return $value;
        }
        return null;
    }

    /**
     * @param AstNode $valueAST
     * @return array
     */
    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->parseValue($valueAST->value);
    }
}