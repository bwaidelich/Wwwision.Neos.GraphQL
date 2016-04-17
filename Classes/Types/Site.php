<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Domain\Model\Site as NeosSite;
use TYPO3\Neos\Domain\Repository\SiteRepository;

/**
 * Scalar type wrapper for \TYPO3\Neos\Domain\Model\Site values
 */
class Site extends ScalarType
{

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @var string
     */
    public $name = 'Site';

    /**
     * @var string
     */
    public $description = 'A site, represented by its node name';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param NeosSite $value
     * @return string
     */
    public function serialize($value)
    {
        if (!$value instanceof NeosSite) {
            return null;
        }
        return $value->getNodeName();
    }

    /**
     * @param string $value
     * @return NeosSite
     */
    public function parseValue($value)
    {
        if (!is_string($value)) {
            return null;
        }
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->siteRepository->findOneByNodeName($value);
    }

    /**
     * @param AstNode $valueAST
     * @return NeosSite
     */
    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->parseValue($valueAST->value);
    }
}