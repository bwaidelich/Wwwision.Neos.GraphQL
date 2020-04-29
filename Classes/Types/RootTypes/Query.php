<?php
namespace Wwwision\Neos\GraphQL\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Neos\Domain\Service\NodeSearchService;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Wwwision\Neos\GraphQL\Types\Context;
use Wwwision\Neos\GraphQL\Types\InputTypes\NodeIdentifierOrPath;
use Wwwision\Neos\GraphQL\Types\Node;
use Wwwision\Neos\GraphQL\Types\NodeType;
use Wwwision\Neos\GraphQL\Types\Scalars;
use Wwwision\Neos\GraphQL\Types\Workspace;

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
     * @Flow\Inject
     * @var NodeSearchService
     */
    protected $nodeSearchService;

    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        parent::__construct([
            'name' => 'Query',
            'description' => 'Root queries for the Neos Content Repository',
            'fields' => function() use ($typeResolver) {
                return [
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
                            'identifier' => ['type' => $typeResolver->get(Scalars\Uuid::class)],
                            'path' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class)],
                        ],
                        'resolve' => function ($_, array $args) {
                            $defaultContext = $this->contextFactory->create();
                            if (isset($args['identifier'])) {
                                return new AccessibleObject($defaultContext->getNodeByIdentifier($args['identifier']));
                            }

                            if (isset($args['path'])) {
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
                            $includeAbstractNodeTypes = $args['includeAbstractNodeTypes'] ?? true;
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

                    'nodesByProperties' => [
                        'type' => Type::listOf($typeResolver->get(Node::class)),
                        'description' => 'Find nodes recursively in the default context, using the NodeSearchService',
                        'args' => [
                            'term' => ['type' => Type::nonNull(Type::string()), 'description' => 'Arbitrary search term'],
                            'searchNodeTypes' => ['type' => Type::nonNull(Type::listOf(Type::string())), 'description' => 'Simple array of Node type names to include in the search result'],
                            'startingPoint' => ['type' => $typeResolver->get(NodeIdentifierOrPath::class), 'description' => 'Optional starting point for the search'],
                        ],
                        'resolve' => function ($_, array $args) {
                            $defaultContext = $this->contextFactory->create();
                            $startingPoint = isset($args['startingPoint']) ? NodeIdentifierOrPath::getNodeFromContext($defaultContext, $args['startingPoint']) : null;
                            return new IterableAccessibleObject($this->nodeSearchService->findByProperties($args['term'], $args['searchNodeTypes'], $defaultContext, $startingPoint));
                        }
                    ],
                ];
            }
        ]);
    }
}
