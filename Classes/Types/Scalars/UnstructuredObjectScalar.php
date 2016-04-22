<?php
namespace Wwwision\Neos\GraphQl\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;

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