<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Language\AST\ListValue;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;

class NodeProperties extends ScalarType
{

    public $name = 'NodeProperties';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function serialize($value)
    {
        if (!is_array($value)) {
            return null;
        }
        return $value;
    }

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

    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->parseValue($valueAST->value);
    }
}