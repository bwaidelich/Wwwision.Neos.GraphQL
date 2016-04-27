<?php
namespace Wwwision\Neos\GraphQL\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Service\NodeOperations;
use TYPO3\TYPO3CR\Domain\Model\Workspace as CRWorkspace;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use TYPO3\TYPO3CR\Domain\Service\PublishingServiceInterface;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Wwwision\Neos\GraphQL\Types\InputTypes;
use Wwwision\Neos\GraphQL\Types\Node;
use Wwwision\Neos\GraphQL\Types\NodePosition;
use Wwwision\Neos\GraphQL\Types\Scalars;
use Wwwision\Neos\GraphQL\Types\Workspace;

/**
 * A GraphQL root definition for all mutations on the root level
 */
class Mutation extends ObjectType
{
    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var NodeOperations
     */
    protected $nodeOperations;

    /**
     * @Flow\Inject
     * @var PublishingServiceInterface
     */
    protected $publishingService;

    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        /** @noinspection PhpUnusedParameterInspection */
        return parent::__construct([
            'name' => 'Mutations',
            'description' => 'Mutations for the Neos Content Repository',
            'fields' => [
                'hideNode' => [
                    'type' => $typeResolver->get(Node::class),
                    'description' => 'Mark a node "hidden" in a given context',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context for this mutation'],
                        'node' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The node to hide'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['node']);
                        $node->setHidden(true);

                        return new AccessibleObject($node);
                    },
                ],
                'createNode' => [
                    'type' => $typeResolver->get(Node::class),
                    'description' => 'Create a node in the given context and return it',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context of this mutation'],
                        'referenceNode' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The reference node for this mutation'],
                        'nodeData' => ['type' => Type::nonNull($typeResolver->get(Scalars\UnstructuredObjectScalar::class)), 'description' => ''],
                        'position' => ['type' => Type::nonNull($typeResolver->get(NodePosition::class)), 'description' => 'Where to create the node to in relation to the reference node'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $referenceNode = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['referenceNode']);

                        // FIXME: We should not rely on the NodeOperations service from the Neos package
                        $createdNode = $this->nodeOperations->create($referenceNode, $args['nodeData'], $args['position']);

                        return new AccessibleObject($createdNode);
                    },
                ],
                'moveNode' => [
                    'type' => $typeResolver->get(Node::class),
                    'description' => 'Move a node in the tree in the given context',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context of this mutation'],
                        'node' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The node to move'],
                        'targetNode' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The reference node for this mutation'],
                        'position' => ['type' => Type::nonNull($typeResolver->get(NodePosition::class)), 'description' => 'Where to move the node to in relation to the target node'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['node']);
                        $targetNode = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['targetNode']);

                        // FIXME: We should not rely on the NodeOperations service from the Neos package
                        $this->nodeOperations->move($node, $targetNode, $args['position']);

                        return new AccessibleObject($node);
                    },
                ],
                'copyNode' => [
                    'type' => $typeResolver->get(Node::class),
                    'description' => 'Copies a node in the tree in the given context',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context of this mutation'],
                        'node' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The node to copy'],
                        'targetNode' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The reference node for this mutation'],
                        'position' => ['type' => Type::nonNull($typeResolver->get(NodePosition::class)), 'description' => 'Where to copy the node to in relation to the target node'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['node']);
                        $targetNode = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['targetNode']);

                        // FIXME: We should not rely on the NodeOperations service from the Neos package
                        $this->nodeOperations->copy($node, $targetNode, $args['position']);

                        return new AccessibleObject($node);
                    },
                ],
                'publishNode' => [
                    'type' => $typeResolver->get(Node::class),
                    'description' => 'Publish a node to some other workspace',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context of this mutation'],
                        'node' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The node to publish'],
                        'targetWorkspace' => ['type' => $typeResolver->get(Scalars\Workspace::class), 'description' => 'The workspace to publish the node to'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $node = $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['node']);
                        if ($node === null) {
                            throw new \InvalidArgumentException(sprintf('The node "%s" could not be found', $args['node']), 1461086537);
                        }
                        $this->publishingService->publishNode($node, isset($args['targetWorkspace']) ? $args['targetWorkspace'] : null);

                        return new AccessibleObject($node);
                    },
                ],
                'publishNodes' => [
                    'type' => Type::listOf($typeResolver->get(Node::class)),
                    'description' => 'Publish the given nodes to another workspace',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context of this mutation'],
                        'nodes' => ['type' => Type::nonNull(Type::listOf($typeResolver->get(InputTypes\NodeIdentifierOrPath::class))), 'description' => 'The list of nodes to be published'],
                        'targetWorkspace' => ['type' => $typeResolver->get(Scalars\Workspace::class), 'description' => 'The workspace to publish the nodes to'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $nodes = [];
                        foreach ($args['nodes'] as $nodePathOrIdentifier) {
                            $node = $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $nodePathOrIdentifier);
                            $nodes[] = $node;
                        }
                        $this->publishingService->publishNodes($nodes, isset($args['targetWorkspace']) ? $args['targetWorkspace'] : null);

                        return new IterableAccessibleObject($nodes);
                    },
                ],
                'publishWorkspace' => [
                    'type' => $typeResolver->get(Workspace::class),
                    'description' => 'Publish all nodes of a given workspace',
                    'args' => [
                        'workspace' => ['type' => Type::nonNull($typeResolver->get(Scalars\Workspace::class)), 'description' => 'The workspace to publish'],
                        'targetWorkspace' => ['type' => Type::nonNull($typeResolver->get(Scalars\Workspace::class)), 'description' => 'The target workspace all nodes should be published to'],
                    ],
                    'resolve' => function ($_, $args) {
                        /** @var CRWorkspace $workspace */
                        $workspace = $args['workspace'];
                        $workspace->publish($args['targetWorkspace']);
                        return new AccessibleObject($workspace);
                    },
                ],
                'discardNode' => [
                    'type' => $typeResolver->get(Node::class),
                    'description' => 'Discard all changes made to a node in a given CR context',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context of this mutation'],
                        'node' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The node to discard'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['node']);
                        $this->publishingService->discardNode($node);

                        return new AccessibleObject($node);
                    },
                ],
                'discardNodes' => [
                    'type' => Type::listOf($typeResolver->get(Node::class)),
                    'description' => 'Discard all changes made to a list of nodes in a given CR context',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context of this mutation'],
                        'nodes' => ['type' => Type::nonNull(Type::listOf($typeResolver->get(InputTypes\NodeIdentifierOrPath::class))), 'description' => 'The nodes to discard'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $nodes = [];
                        foreach ($args['nodes'] as $nodePathOrIdentifier) {
                            $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $nodePathOrIdentifier);
                            $nodes[] = $node;
                        }
                        $this->publishingService->discardNodes($nodes);

                        return new IterableAccessibleObject($nodes);
                    },
                ],
                'discardAllNodes' => [
                    'type' => $typeResolver->get(Workspace::class),
                    'description' => 'Discard all changes to all nodes of a given workspace',
                    'args' => [
                        'workspace' => ['type' => Type::nonNull($typeResolver->get(Scalars\Workspace::class)), 'description' => 'The workspace for which nodes should be discarded'],
                    ],
                    'resolve' => function ($_, $args) {
                        $this->publishingService->discardAllNodes($args['workspace']);
                        return new AccessibleObject($args['workspace']);
                    },
                ],
            ]
        ]);
    }
}