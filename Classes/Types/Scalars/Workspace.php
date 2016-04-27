<?php
namespace Wwwision\Neos\GraphQL\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\Workspace as NeosWorkspace;
use TYPO3\TYPO3CR\Domain\Repository\WorkspaceRepository;

/**
 * Scalar type wrapper for \TYPO3\TYPO3CR\Domain\Model\Workspace values
 */
class Workspace extends ScalarType
{

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @var string
     */
    public $name = 'WorkspaceScalar';

    /**
     * @var string
     */
    public $description = 'A workspace, represented by its name';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param NeosWorkspace $value
     * @return string
     */
    public function serialize($value)
    {
        if (!$value instanceof NeosWorkspace) {
            return null;
        }
        return $value->getName();
    }

    /**
     * @param string $value
     * @return NeosWorkspace
     */
    public function parseValue($value)
    {
        if (!is_string($value)) {
            return null;
        }
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->workspaceRepository->findOneByName($value);
    }

    /**
     * @param AstNode $valueAST
     * @return NeosWorkspace
     */
    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->parseValue($valueAST->value);
    }
}