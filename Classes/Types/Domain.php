<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Domain\Model\Domain as NeosDomain;
use TYPO3\Neos\Domain\Repository\DomainRepository;

/**
 * Scalar type wrapper for \TYPO3\Neos\Domain\Model\Domain values
 */
class Domain extends ScalarType
{

    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * @var string
     */
    public $name = 'Domain';

    /**
     * @var string
     */
    public $description = 'A domain, represented by its host name';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param NeosDomain $value
     * @return string
     */
    public function serialize($value)
    {
        if (!$value instanceof NeosDomain) {
            return null;
        }
        return $value->getHostPattern();
    }

    /**
     * @param string $value
     * @return NeosDomain
     */
    public function parseValue($value)
    {
        if (!is_string($value)) {
            return null;
        }
        return $this->domainRepository->findOneByHost($value, true);
    }

    /**
     * @param AstNode $valueAST
     * @return NeosDomain
     */
    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->parseValue($valueAST->value);
    }
}