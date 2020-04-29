<?php
namespace Wwwision\Neos\GraphQL\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

/**
 * Represents an absolute node path in the form "some/relative/path" (no leading slash)
 */
class RelativeNodePath extends ScalarType
{

    /**
     * @var string
     */
    public $name = 'RelativeNodePathScalar';

    /**
     * @var string
     */
    public $description = 'A relative node path in the form "some/relative/path" (no leading slash)';

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
     * @param array $variables
     * @return string
     */
    public function parseLiteral($valueAST, ?array $variables = null)
    {
        if (!$valueAST instanceof StringValueNode) {
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
        if (!\is_string($value) || $value[0] === '/') {
            return null;
        }
        return $value;
    }
}
