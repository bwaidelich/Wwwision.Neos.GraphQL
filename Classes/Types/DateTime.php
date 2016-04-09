<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;

class DateTime extends ScalarType
{

    public $name = 'DateTime';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function serialize($value)
    {
        if (!$value instanceof \DateTimeInterface) {
            return null;
        }
        return $value->format(DATE_ISO8601);
    }

    public function parseValue($value)
    {
        $dateTime = \DateTimeImmutable::createFromFormat(DATE_ISO8601, $value);
        if ($dateTime === false) {
            return null;
        }
        return $dateTime;
    }

    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->parseValue($valueAST->value);
    }
}