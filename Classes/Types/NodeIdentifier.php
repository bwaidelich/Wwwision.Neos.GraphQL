<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;

class NodeIdentifier extends ScalarType
{
    /**
     * @var string
     */
    const PATTERN_MATCH_UUID = '/^([a-f0-9]){8}-([a-f0-9]){4}-([a-f0-9]){4}-([a-f0-9]){4}-([a-f0-9]){12}$/';

    public $name = 'NodeIdentifier';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function serialize($value)
    {
        return self::isNodeIdentifier($value) ? $value : null;
    }

    public function parseValue($value)
    {
        return self::isNodeIdentifier($value) ? $value : null;
    }

    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->parseValue($valueAST->value);
    }

    static public function isNodeIdentifier($value)
    {
        if (!is_string($value) || preg_match(self::PATTERN_MATCH_UUID, $value) !== 1) {
            return null;
        }
        return $value;
    }

}