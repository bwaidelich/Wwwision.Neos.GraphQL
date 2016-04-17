<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use Wwwision\Neos\GraphQl\TypeResolver;
use Wwwision\Neos\GraphQl\Types\Wrapper\AccessibleObject;
use Wwwision\Neos\GraphQl\Types\Wrapper\IterableAccessibleObject;

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
        return parent::__construct([
            'name' => 'Node',
            'fields' => [
                'name' => ['type' => Type::string()],
                'label' => ['type' => Type::string()],
                'propertyNames' => ['type' => Type::listOf(Type::string())],
                'properties' => ['type' => $typeResolver->get(NodeProperties::class)],
                'hidden' => ['type' => Type::boolean()],
                'hiddenBeforeDateTime' => ['type' => $typeResolver->get(DateTime::class)],
                'hiddenAfterDateTime' => ['type' => $typeResolver->get(DateTime::class)],
                'hiddenInIndex' => ['type' => Type::boolean()],
                'removed' => ['type' => Type::boolean()],
                'visible' => ['type' => Type::boolean()],
                'accessible' => ['type' => Type::boolean()],
                'accessRestrictions' => ['type' => Type::boolean()],
                'accessRoles' => ['type' => Type::listOf(Type::string())],
                'autoCreated' => ['type' => Type::boolean()],
                'path' => ['type' => Type::string()],
                'contextPath' => ['type' => Type::string()],
                'depth' => ['type' => Type::int()],
                'workspace' => ['type' => $typeResolver->get(Workspace::class)],
                'identifier' => ['type' => Type::string()],
                'index' => ['type' => Type::int()],
                'parent' => ['type' => function() use ($typeResolver) {return $typeResolver->get(Node::class);}],
                'parentPath' => ['type' => Type::string()],
                'primaryChildNode' => ['type' => function() use ($typeResolver) {return $typeResolver->get(Node::class);}],
                'childNodes' => [
                    'type' => Type::listOf($typeResolver->get(Node::class)),
                    'args' => [
                        'nodeTypeFilter' => ['type' => Type::string()],
                        'limit' => ['type' => Type::int()],
                        'offset' => ['type' => Type::int()],
                        'recursive' => ['type' => Type::boolean()]
                    ],
                    'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                        /** @var NodeInterface $node */
                        $node = $wrappedNode->getObject();
                        $nodeTypeFilter = isset($args['nodeTypeFilter']) ? $args['nodeTypeFilter'] : null;
                        $limit = isset($args['limit']) ? $args['limit'] : null;
                        $offset = isset($args['offset']) ? $args['offset'] : null;
                        return new IterableAccessibleObject($node->getChildNodes($nodeTypeFilter, $limit, $offset));
                    }
                ],
                'nodeAllowedAsChildNode' => [
                    'type' => Type::boolean(),
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
                'nodeByPath' => [
                    'type' => $typeResolver->get(Node::class),
                    'args' => [
                        'path' => ['type' => Type::nonNull($typeResolver->get(RelativeNodePath::class))],
                    ],
                    'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                        /** @var NodeInterface $node */
                        $node = $wrappedNode->getObject();
                        return new AccessibleObject($node->getNode($args['path']));
                    }
                ],
                'hasChildNodes' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'nodeTypeFilter' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                        /** @var NodeInterface $node */
                        $node = $wrappedNode->getObject();
                        return $node->hasChildNodes(isset($args['nodeTypeFilter']) ? $args['nodeTypeFilter'] : null);
                    }
                ],
                'nodeType' => ['type' => $typeResolver->get(NodeType::class)],
                'dimensions' => ['type' => $typeResolver->get(Dimensions::class)],
                'context' => ['type' => $typeResolver->get(Context::class)],
                'otherNodeVariants' => [
                    'type' => Type::listOf($typeResolver->get(Node::class)),
                    'resolve' => function (AccessibleObject $wrappedNode) {
                        /** @var NodeInterface $node */
                        $node = $wrappedNode->getObject();
                        return new IterableAccessibleObject($node->getOtherNodeVariants());
                    }],
            ],
        ]);
    }
}