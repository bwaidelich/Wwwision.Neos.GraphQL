<?php
namespace Wwwision\Neos\GraphQL\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

/**
 * Scalar type wrapper for \DateTimeInterface values
 */
class DateTime extends ScalarType
{

    /**
     * @var string
     */
    public $name = 'DateTimeScalar';

    /**
     * @var string
     */
    public $description = 'A Date and time, represented as ISO 8601 conform string';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param \DateTimeInterface $value
     * @return string
     */
    public function serialize($value)
    {
        if (!$value instanceof \DateTimeInterface) {
            return null;
        }
        return $value->format(DATE_ATOM);
    }

    /**
     * @param string $value
     * @return \DateTimeImmutable
     */
    public function parseValue($value)
    {
        if (!\is_string($value)) {
            return null;
        }
        $dateTime = \DateTimeImmutable::createFromFormat(DATE_ATOM, $value);
        if ($dateTime === false) {
            return null;
        }
        return $dateTime;
    }

    /**
     * @param AstNode $valueAST
     * @param array $variables
     * @return \DateTimeImmutable
     */
    public function parseLiteral($valueAST, ?array $variables = null)
    {
        if (!$valueAST instanceof StringValueNode) {
            return null;
        }
        return $this->parseValue($valueAST->value);
    }
}
