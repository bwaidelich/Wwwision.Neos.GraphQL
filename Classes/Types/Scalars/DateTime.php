<?php
namespace Wwwision\Neos\GraphQl\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;

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
        return $value->format(DATE_ISO8601);
    }

    /**
     * @param string $value
     * @return \DateTimeImmutable
     */
    public function parseValue($value)
    {
        if (!is_string($value)) {
            return null;
        }
        $dateTime = \DateTimeImmutable::createFromFormat(DATE_ISO8601, $value);
        if ($dateTime === false) {
            return null;
        }
        return $dateTime;
    }

    /**
     * @param AstNode $valueAST
     * @return \DateTimeImmutable
     */
    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->parseValue($valueAST->value);
    }
}