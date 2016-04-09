<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;

class AbsoluteNodePath extends ScalarType
{

    public $name = 'AbsoluteNodePath';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function serialize($value)
    {
        return $this->coerceNodePath($value);
    }

    public function parseValue($value)
    {
        return $this->coerceNodePath($value);
    }

    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->coerceNodePath($valueAST->value);
    }

    private function coerceNodePath($value)
    {
        if (!is_string($value) || substr($value, 0, 1) !== '/') {
            return null;
        }
        return $value;
    }
}