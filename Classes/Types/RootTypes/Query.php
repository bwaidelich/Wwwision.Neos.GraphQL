<?php
namespace Wwwision\Neos\GraphQl\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Repository\WorkspaceRepository;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use Wwwision\Neos\GraphQl\TypeResolver;
use Wwwision\Neos\GraphQl\Types\Context;
use Wwwision\Neos\GraphQl\Types\Node;
use Wwwision\Neos\GraphQl\Types\NodeType;
use Wwwision\Neos\GraphQl\Types\Scalars;
use Wwwision\Neos\GraphQl\Types\Workspace;
use Wwwision\Neos\GraphQl\Types\Wrapper\AccessibleObject;
use Wwwision\Neos\GraphQl\Types\Wrapper\IterableAccessibleObject;

/**
 * A GraphQL root definition for all queries on the root level
 */
class Query extends ObjectType
{

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        /** @noinspection PhpUnusedParameterInspection */
        return parent::__construct([
            'name' => 'Query',
            'description' => 'Root queries for the Neos Content Repository',
            'fields' => [
                'context' => [
                    'type' => $typeResolver->get(Context::class),
                    'args' => [
                        'workspaceName' => ['type' => Type::string(), 'description' => 'The name of the workspace'],
                        'currentDateTime' => ['type' => $typeResolver->get(Scalars\DateTime::class), 'description' => 'The date and time to simulate (ISO 8601 format)'],
                        'dimensions' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'description' => 'Dimensions and their values'],
                        'targetDimensions' => ['type' => Type::string(), 'description' => 'Dimensions and their values when creating new nodes'],
                        'invisibleContentShown' => ['type' => Type::boolean(), 'description' => 'Whether nodes marked "hidden" should be shown in this context'],
                        'removedContentShown' => ['type' => Type::boolean(), 'description' => 'Whether nodes marked "hidden" should be shown in this context'],
                        'inaccessibleContentShown' => ['type' => Type::boolean(), 'description' => 'Whether nodes marked "hidden" should be shown in this context'],
                    ],
                    'resolve' => function ($_, $contextConfiguration) {
                        $context = $this->contextFactory->create($contextConfiguration);
                        return new AccessibleObject($context);
                    },
                ],
                'node' => [
                    'type' => $typeResolver->get(Node::class),
                    'args' => [
                        'identifier' => ['type' => $typeResolver->get(Scalars\NodeIdentifier::class)],
                        'path' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class)],
                    ],
                    'resolve' => function ($_, array $args) {
                        $defaultContext = $this->contextFactory->create();
                        if (isset($args['identifier'])) {
                            return new AccessibleObject($defaultContext->getNodeByIdentifier($args['identifier']));
                        } elseif (isset($args['path'])) {
                            return new AccessibleObject($defaultContext->getNode($args['path']));
                        }
                        throw new \InvalidArgumentException('node path or identifier have to be specified!', 1460064707);
                    }
                ],
                'rootNode' => [
                    'type' => $typeResolver->get(Node::class),
                    'resolve' => function ($_) {
                        $defaultContext = $this->contextFactory->create();
                        return new AccessibleObject($defaultContext->getRootNode());
                    },
                ],
               'nodesOnPath' => [
                    'type' => Type::listOf($typeResolver->get(Node::class)),
                    'args' => [
                        'start' => ['type' => Type::nonNull($typeResolver->get(Scalars\AbsoluteNodePath::class))],
                        'end' => ['type' => Type::nonNull($typeResolver->get(Scalars\AbsoluteNodePath::class))],
                    ],
                    'resolve' => function ($_, array $args) {
                        $defaultContext = $this->contextFactory->create();

                        return new IterableAccessibleObject($defaultContext->getNodesOnPath($args['start'], $args['end']));
                    }
                ],
               'workspace' => [
                    'type' => $typeResolver->get(Workspace::class),
                    'description' => 'A Content Repository workspace',
                    'args' => [
                        'name' => ['type' => Type::nonNull(Type::string()), 'description' => 'Name of the workspace to retrieve'],
                    ],
                    'resolve' => function ($_, array $args) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $workspace = $this->workspaceRepository->findOneByName($args['name']);

                        if ($workspace === null) {
                            throw new \InvalidArgumentException(sprintf('A workspace named "%s" could not be found.', $args['name']), 1461323974);
                        }

                        return new AccessibleObject($workspace);
                    }
                ],
               'workspaces' => [
                    'type' => Type::listOf($typeResolver->get(Workspace::class)),
                    'description' => 'A list of all Content Repository workspaces',
                    'resolve' => function ($_) {
                        return new IterableAccessibleObject($this->workspaceRepository->findAll());
                    }
                ],
               'nodeType' => [
                    'type' => $typeResolver->get(NodeType::class),
                    'description' => 'The specified node type (which could be abstract)',
                    'args' => [
                        'nodeTypeName' => ['type' => Type::nonNull(Type::string()), 'description' => 'The node type identifier'],
                    ],
                    'resolve' => function ($_, array $args) {
                        return new AccessibleObject($this->nodeTypeManager->getNodeType($args['nodeTypeName']));
                    }
                ],
               'nodeTypes' => [
                    'type' => Type::listOf($typeResolver->get(NodeType::class)),
                    'description' => 'A list of all registered node types',
                    'args' => [
                        'includeAbstractNodeTypes' => ['type' => Type::boolean(), 'description' => 'Whether to include abstract node types, defaults to TRUE'],
                    ],
                    'resolve' => function ($_, array $args) {
                        $includeAbstractNodeTypes = isset($args['includeAbstractNodeTypes']) ? $args['includeAbstractNodeTypes'] : true;
                        return new IterableAccessibleObject($this->nodeTypeManager->getNodeTypes($includeAbstractNodeTypes));
                    }
                ],
               'hasNodeType' => [
                    'type' => Type::boolean(),
                    'description' => 'Whether the specified node type is registered (including abstract node types)',
                    'args' => [
                        'nodeTypeName' => ['type' => Type::nonNull(Type::string()), 'description' => 'The node type identifier'],
                    ],
                    'resolve' => function ($_, array $args) {
                        return $this->nodeTypeManager->hasNodeType($args['nodeTypeName']);
                    }
                ],
            ]
        ]);
    }
}