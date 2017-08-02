<?php
namespace Wwwision\Neos\GraphQL\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Workspace as CRWorkspace;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeServiceInterface;
use Neos\ContentRepository\Domain\Service\PublishingServiceInterface;
use Neos\ContentRepository\Domain\Utility\NodePaths;
use Wwwision\GraphQL\TypeResolver;
use Wwwision\Neos\GraphQL\Types\InputTypes;
use Wwwision\Neos\GraphQL\Types\MutationResult;
use Wwwision\Neos\GraphQL\Types\NodePosition;
use Wwwision\Neos\GraphQL\Types\Scalars;

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
     * @var NodeServiceInterface
     */
    protected $nodeService;

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
                    'type' => $typeResolver->get(MutationResult::class),
                    'description' => 'Mark a node "hidden" in a given context',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context for this mutation'],
                        'node' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The node to hide'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['node']);
                        $node->setHidden(true);

                        return ['success' => true];
                    },
                ],
                'removeNode' => [
                    'type' => $typeResolver->get(MutationResult::class),
                    'description' => 'Removes a node from the given context',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context for this mutation'],
                        'node' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The node to remove'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['node']);
                        $node->remove();

                        return ['success' => true];
                    },
                ],
                'createNode' => [
                    'type' => $typeResolver->get(MutationResult::class),
                    'description' => 'Create a node in the given context and return it',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context of this mutation'],
                        'referenceNode' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The reference node for this mutation'],
                        'position' => ['type' => Type::nonNull($typeResolver->get(NodePosition::class)), 'description' => 'Where to create the node to in relation to the reference node'],
                        'nodeType' => ['type' => $typeResolver->get(InputTypes\NodeType::class), 'description' => 'Optional type of node to create (if omitted an unstructured node is created)'],
                        'properties' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'description' => 'Optional properties of the new node'],
                        'name' => ['type' => Type::string(), 'description' => 'Optional proposed node name (if not unique this will be tampered)'],
                        'identifier' => ['type' => $typeResolver->get(Scalars\Uuid::class), 'description' => 'Optional unique identifier of the node to create (use with care!)'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $referenceNode = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['referenceNode']);
                        $designatedParentNode = $this->getDesignatedParentNode($referenceNode, $args['position']);

                        // Note: The following lines are "inspired" from the NodeOperations service from the Neos package. We plan to move this logic into the CR package at some point
                        $proposedNodeName = isset($args['name']) ? $args['name'] : null;
                        $nodeName = $this->nodeService->generateUniqueNodeName($designatedParentNode->getPath(), $proposedNodeName);
                        $nodeType = isset($args['nodeType']) ? $args['nodeType'] : null;
                        $nodeIdentifier = isset($args['identifier']) ? $args['identifier'] : null;
                        $newNode = $designatedParentNode->createNode($nodeName, $nodeType, $nodeIdentifier);
                        if ($args['position'] === 'before') {
                            $newNode->moveBefore($referenceNode);
                        } elseif ($args['position'] === 'after') {
                            $newNode->moveAfter($referenceNode);
                        }

                        if (isset($args['properties'])) {
                            foreach ($args['properties'] as $propertyName => $propertyValue) {
                                $newNode->setProperty($propertyName, $propertyValue);
                            }
                        }
                        return ['success' => true];
                    },
                ],
                'moveNode' => [
                    'type' => $typeResolver->get(MutationResult::class),
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

                        // Note: The following lines are "inspired" from the NodeOperations service from the Neos package. We plan to move this logic into the CR package at some point
                        $designatedParentNode = $this->getDesignatedParentNode($targetNode, $args['position']);
                        // If we stay inside the same parent we basically just reorder, no rename needed or wanted.
                        if ($designatedParentNode !== $node->getParent()) {
                            $designatedNodePath = NodePaths::addNodePathSegment($designatedParentNode->getPath(), $node->getName());
                            if ($this->nodeService->nodePathAvailableForNode($designatedNodePath, $node) === false) {
                                $nodeName = $this->nodeService->generateUniqueNodeName($designatedParentNode->getPath(), $node->getName());
                                if ($nodeName !== $node->getName()) {
                                    $node->setName($nodeName);
                                }
                            }
                        }
                        switch ($args['position']) {
                            case 'before':
                                $node->moveBefore($targetNode);
                                break;
                            case 'into':
                                $node->moveInto($targetNode);
                                break;
                            case 'after':
                                $node->moveAfter($targetNode);
                        }

                        return ['success' => true];
                    },
                ],
                'copyNode' => [
                    'type' => $typeResolver->get(MutationResult::class),
                    'description' => 'Copies a node in the tree in the given context',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context of this mutation'],
                        'node' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The node to copy'],
                        'targetNode' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The reference node for this mutation'],
                        'position' => ['type' => Type::nonNull($typeResolver->get(NodePosition::class)), 'description' => 'Where to copy the node to in relation to the target node'],
                        'name' => ['type' => Type::string(), 'description' => 'Optional proposed node name for the new copy (if not unique this will be tampered)'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['node']);
                        $targetNode = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['targetNode']);

                        // Note: The following lines are "inspired" from the NodeOperations service from the Neos package. We plan to move this logic into the CR package at some point
                        $proposedNodeName = isset($args['name']) ? $args['name'] : null;
                        $nodeName = $this->nodeService->generateUniqueNodeName($this->getDesignatedParentNode($targetNode, $args['position'])->getPath(), $proposedNodeName);

                        switch ($args['position']) {
                            case 'before':
                                $node->copyBefore($targetNode, $nodeName);
                                break;
                            case 'after':
                                $node->copyAfter($targetNode, $nodeName);
                                break;
                            case 'into':
                            default:
                                $node->copyInto($targetNode, $nodeName);
                        }

                        return ['success' => true];
                    },
                ],
                'publishNode' => [
                    'type' => $typeResolver->get(MutationResult::class),
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

                        return ['success' => true];
                    },
                ],
                'publishNodes' => [
                    'type' => $typeResolver->get(MutationResult::class),
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

                        return ['success' => true];
                    },
                ],
                'publishWorkspace' => [
                    'type' => $typeResolver->get(MutationResult::class),
                    'description' => 'Publish all nodes of a given workspace',
                    'args' => [
                        'workspace' => ['type' => Type::nonNull($typeResolver->get(Scalars\Workspace::class)), 'description' => 'The workspace to publish'],
                        'targetWorkspace' => ['type' => $typeResolver->get(Scalars\Workspace::class), 'description' => 'Optional target workspace all nodes should be published to (if omitted the workspace\'s base workspace is assumed)'],
                    ],
                    'resolve' => function ($_, $args) {
                        /** @var CRWorkspace $workspace */
                        $workspace = $args['workspace'];
                        $targetWorkspace = isset($args['targetWorkspace']) ? $args['targetWorkspace'] : null;

                        // Note: We don't use Workspace::publish() because that does not trigger signals to flush caches etc..

                        $unpublishedNodes = $this->publishingService->getUnpublishedNodes($workspace);
                        $this->publishingService->publishNodes($unpublishedNodes, $targetWorkspace);

                        return ['success' => true];
                    },
                ],
                'discardNode' => [
                    'type' => $typeResolver->get(MutationResult::class),
                    'description' => 'Discard all changes made to a node in a given CR context',
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(InputTypes\Context::class)), 'description' => 'The CR context of this mutation'],
                        'node' => ['type' => Type::nonNull($typeResolver->get(InputTypes\NodeIdentifierOrPath::class)), 'description' => 'The node to discard'],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);
                        $node = InputTypes\NodeIdentifierOrPath::getNodeFromContext($context, $args['node']);
                        $this->publishingService->discardNode($node);

                        return ['success' => true];
                    },
                ],
                'discardNodes' => [
                    'type' => $typeResolver->get(MutationResult::class),
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

                        return ['success' => true];
                    },
                ],
                'discardAllNodes' => [
                    'type' => $typeResolver->get(MutationResult::class),
                    'description' => 'Discard all changes to all nodes of a given workspace',
                    'args' => [
                        'workspace' => ['type' => Type::nonNull($typeResolver->get(Scalars\Workspace::class)), 'description' => 'The workspace for which nodes should be discarded'],
                    ],
                    'resolve' => function ($_, $args) {
                        $this->publishingService->discardAllNodes($args['workspace']);

                        return ['success' => true];
                    },
                ],
            ]
        ]);
    }

    /**
     * @param NodeInterface $targetNode
     * @param string $position
     * @return NodeInterface
     */
    protected function getDesignatedParentNode(NodeInterface $targetNode, $position)
    {
        $referenceNode = $targetNode;
        if (in_array($position, array('before', 'after'))) {
            $referenceNode = $targetNode->getParent();
        }

        return $referenceNode;
    }
}