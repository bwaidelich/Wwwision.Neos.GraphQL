<?php
namespace Wwwision\Neos\GraphQl\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;

/**
 * Represents an absolute node path in the form "/sites/some-site/some/path" (including leading slash)
 */
class AbsoluteNodePath extends ScalarType
{

    /**
     * @var string
     */
    public $name = 'AbsoluteNodePathScalar';

    /**
     * @var string
     */
    public $description = 'An absolute node path in the form "/sites/some-site/some/path" (including leading slash)';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $value
     * @return string
     */
    public function serialize($value)
    {
        return $this->coerceNodePath($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function parseValue($value)
    {
        return $this->coerceNodePath($value);
    }

    /**
     * @param AstNode $valueAST
     * @return string
     */
    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->coerceNodePath($valueAST->value);
    }

    /**
     * @param string $value
     * @return string
     */
    private function coerceNodePath($value)
    {
        if (!is_string($value) || substr($value, 0, 1) !== '/') {
            return null;
        }
        return $value;
    }
}