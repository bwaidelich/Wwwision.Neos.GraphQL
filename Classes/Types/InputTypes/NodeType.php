<?php
namespace Wwwision\Neos\GraphQL\Types\InputTypes;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeType as CRNodeType;
use TYPO3\TYPO3CR\Domain\Service\Context as CRContext;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use TYPO3\TYPO3CR\Exception\NodeTypeNotFoundException;
use Wwwision\Neos\GraphQL\Types\Scalars\AbsoluteNodePath;
use Wwwision\Neos\GraphQL\Types\Scalars\Uuid;

/**
 * A node represented by its identifier (UUID) or absolute path
 */
class NodeType extends ScalarType
{

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @var string
     */
    public $name = 'NodeTypeInput';

    /**
     * @var string
     */
    public $description = 'A node type represented by its unique name (e.g. "Some.Package:Type")';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param CRNodeType $value
     * @return string
     */
    public function serialize($value)
    {
        if (!$value instanceof CRNodeType) {
            return null;
        }
        return $value->getName();
    }

    /**
     * @param string $value
     * @return string
     */
    public function parseValue($value)
    {
        if (!is_string($value)) {
            return null;
        }
        try {
            $nodeType = $this->nodeTypeManager->getNodeType($value);
        } catch (NodeTypeNotFoundException $exception) {
            return null;
        }
        return $nodeType;
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
        return $this->parseValue($valueAST->value);
    }

}