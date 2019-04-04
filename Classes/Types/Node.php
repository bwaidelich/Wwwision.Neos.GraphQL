<?php
namespace Wwwision\Neos\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Wwwision\Neos\GraphQL\Types\Scalars\AbsoluteNodePath;
use Wwwision\Neos\GraphQL\Types\Scalars\Uuid;

/**
 * A GraphQL type definition describing a Neos\ContentRepository\Domain\Model\NodeInterface
 */
class Node extends ObjectType
{

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
        parent::__construct([
            'name' => 'Node',
            'description' => 'A Node of the Content Repository',
            'fields' => function() use ($typeResolver) {
                return [
                    'name' => ['type' => Type::string(), 'description' => 'Name of this node'],
                    'label' => ['type' => Type::string(), 'description' => 'Full length plain text label of this node'],
                    'hasProperty' => [
                        'type' => Type::boolean(),
                        'description' => 'If this node has a property with the given name',
                        'args' => [
                            'propertyName' => ['type' => Type::nonNull(Type::string())],
                        ],
                        'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                            /** @var NodeInterface $node */
                            $node = $wrappedNode->getObject();
                            return $node->hasProperty($args['propertyName']);
                        }
                    ],
                    'propertyNames' => ['type' => Type::listOf(Type::string()), 'description' => 'The names of all properties of this node'],
                    'properties' => ['type' => $typeResolver->get(Scalars\NodePropertiesScalar::class), 'description' => 'All properties of this node'],
                    'isHidden' => ['type' => Type::boolean(), 'description' => 'Whether this node is marked hidden'],
                    'hiddenBeforeDateTime' => ['type' => $typeResolver->get(Scalars\DateTime::class), 'description' => 'The date and time before which this node will be automatically hidden'],
                    'hiddenAfterDateTime' => ['type' => $typeResolver->get(Scalars\DateTime::class), 'description' => 'The node and time after which this node will be hidden'],
                    'isHiddenInIndex' => ['type' => Type::boolean(), 'description' => 'Whether this node should be hidden in indexes'],
                    'isRemoved' => ['type' => Type::boolean(), 'description' => 'Whether this node has been removed'],
                    'isVisible' => ['type' => Type::boolean(), 'description' => 'Whether this node is visible (depending on hidden flag, hiddenBeforeDateTime and hiddenAfterDateTime)'],
                    'isAccessible' => ['type' => Type::boolean(), 'description' => 'Whether this node may be accessed according to the current security context'],
                    'hasAccessRestrictions' => ['type' => Type::boolean(), 'description' => 'Whether this node as access restrictions applied'],
                    'accessRoles' => ['type' => Type::listOf(Type::string()), 'description' => 'The names of defined access roles'],
                    'isAutoCreated' => ['type' => Type::boolean(), 'description' => 'Whether this node is configured as auto-created childNode of its parent'],
                    'path' => ['type' => $typeResolver->get(AbsoluteNodePath::class), 'description' => 'The absolute path of tis node'],
                    'contextPath' => ['type' => Type::string(), 'description' => 'The absolute path of this node including context information'],
                    'depth' => ['type' => Type::int(), 'description' => 'The level at which this node is located in the tree'],
                    'workspace' => ['type' => $typeResolver->get(Workspace::class), 'description' => 'The workspace this node is contained in'],
                    'identifier' => ['type' => $typeResolver->get(Uuid::class), 'description' => 'The identifier of this node (not the technical id)'],
                    'index' => ['type' => Type::int(), 'deprecationReason' => 'Not part of the public API', 'description' => 'The index of this node among its siblings'],
                    'parent' => ['type' => $typeResolver->get(Node::class), 'description' => 'The parent node of this node'],
                    'parentPath' => ['type' => $typeResolver->get(AbsoluteNodePath::class), 'description' => 'The parent node path'],
                    'primaryChildNode' => ['type' => $typeResolver->get(Node::class), 'description' => 'The primary child node of this node, if it exists'],
//                    'childNodes' => [
//                        'type' => Type::listOf($typeResolver->get(Node::class)),
//                        'description' => 'All direct child nodes of this node, optionally filtered by type',
//                        'args' => [
//                            'nodeTypeFilter' => ['type' => Type::string()],
//                            'limit' => ['type' => Type::int()],
//                            'offset' => ['type' => Type::int()],
//                        ],
//                        'resolve' => function (AccessibleObject $wrappedNode, array $args) {
//                            /** @var NodeInterface $node */
//                            $node = $wrappedNode->getObject();
//                            $nodeTypeFilter = isset($args['nodeTypeFilter']) ? $args['nodeTypeFilter'] : null;
//                            $limit = isset($args['limit']) ? $args['limit'] : null;
//                            $offset = isset($args['offset']) ? $args['offset'] : null;
//                            return new IterableAccessibleObject($node->getChildNodes($nodeTypeFilter, $limit, $offset));
//                        }
//                    ],
                    'isNodeAllowedAsChildNode' => [
                        'type' => Type::boolean(),
                        'deprecationReason' => 'Not part of the public API',
                        'description' => 'Whether the given node type would ba allowed as child node of this node according to the configured constraints',
                        'args' => [
                            'nodeType' => ['type' => Type::nonNull(Type::string())],
                        ],
                        'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                            /** @var NodeInterface $node */
                            $node = $wrappedNode->getObject();
                            $nodeType = $this->nodeTypeManager->getNodeType($args['nodeType']);
                            return $node->isNodeTypeAllowedAsChildNode($nodeType);
                        }
                    ],
                    'node' => [
                        'type' => $typeResolver->get(Node::class),
                        'description' => 'A node specified by the given relative path',
                        'args' => [
                            'path' => ['type' => Type::nonNull($typeResolver->get(Scalars\RelativeNodePath::class))],
                        ],
                        'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                            /** @var NodeInterface $node */
                            $node = $wrappedNode->getObject();
                            return new AccessibleObject($node->getNode($args['path']));
                        }
                    ],
                    'hasChildNodes' => [
                        'type' => Type::boolean(),
                        'description' => 'Whether this node has any child nodes (that match the optional type filter)',
                        'args' => [
                            'nodeTypeFilter' => ['type' => Type::string()],
                        ],
                        'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                            /** @var NodeInterface $node */
                            $node = $wrappedNode->getObject();
                            return $node->hasChildNodes(isset($args['nodeTypeFilter']) ? $args['nodeTypeFilter'] : null);
                        }
                    ],
                    'nodeType' => ['type' => $typeResolver->get(NodeType::class), 'description' => 'The node type of this node'],
                    'dimensions' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'deprecationReason' => 'Not part of the public API', 'description' => 'The content dimensions assigned to this node'],
                    'context' => ['type' => $typeResolver->get(Context::class), 'deprecationReason' => 'Not part of the public API', 'description' => 'The context of this node'],
//                    'otherNodeVariants' => [
//                        'type' => Type::listOf($typeResolver->get(Node::class)),
//                        'deprecationReason' => 'Not part of the public API',
//                        'description' => 'Other variants of this very node (with different dimension values)',
//                        'resolve' => function (AccessibleObject $wrappedNode) {
//                            /** @var NodeInterface $node */
//                            $node = $wrappedNode->getObject();
//                            return new IterableAccessibleObject($node->getOtherNodeVariants());
//                        }
//                    ],
                ];
            }
        ]);
    }
}
