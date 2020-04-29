<?php
namespace Wwwision\Neos\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeType as CRNodeType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;

/**
 * A GraphQL type definition describing a Neos\ContentRepository\Domain\Model\NodeType
 */
class NodeType extends ObjectType
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
            'name' => 'NodeType',
            'description' => 'A node type',
            'fields' => function () use ($typeResolver) {
                return [
                    'name' => ['type' => Type::string(), 'description' => 'Name of this node type, e.g. "Neos.Neos:Document"'],
                    'isAbstract' => [
                        'type' => Type::boolean(),
                        'deprecationReason' => 'Not part of the public API',
                        'description' => 'Whether or not this node type is marked abstract',
                        'resolve' => static function (AccessibleObject $wrappedNodeType) {
                            return $wrappedNodeType->getObject()->isAbstract();
                        }
                    ],
                    'isFinal' => [
                        'type' => Type::boolean(),
                        'deprecationReason' => 'Not part of the public API',
                        'description' => 'Whether or not this node type is marked final',
                        'resolve' => static function (AccessibleObject $wrappedNodeType) {
                            return $wrappedNodeType->getObject()->isFinal();
                        }
                    ],
                    #'declaredSuperTypes' => ['type' => Type::listOf($typeResolver->get(NodeType::class)), 'description' => 'The direct, explicitly declared super types of this node type'],
                    'isAggregate' => [
                        'type' => Type::boolean(),
                        'description' => 'Whether or not this node type is an aggregate. The most prominent aggregate is a Document',
                        'resolve' => static function (AccessibleObject $wrappedNodeType) {
                            return $wrappedNodeType->getObject()->isAggregate();
                        }
                    ],
                    'isOfType' => [
                        'type' => Type::boolean(),
                        'description' => 'If this node type or any of the direct or indirect super types has the given name',
                        'args' => [
                            'nodeType' => ['type' => Type::nonNull(Type::string())],
                        ],
                        'resolve' => static function (AccessibleObject $wrappedNodeType, array $args) {
                            /** @var CRNodeType $nodeType */
                            $nodeType = $wrappedNodeType->getObject();
                            return $nodeType->isOfType($args['nodeType']);
                        }
                    ],
                    'hasConfiguration' => [
                        'type' => Type::boolean(),
                        'description' => 'Checks if the configuration of this node type contains a setting for the given configurationPath',
                        'args' => [
                            'configurationPath' => ['type' => Type::nonNull(Type::string())],
                        ],
                        'resolve' => static function (AccessibleObject $wrappedNodeType, array $args) {
                            /** @var CRNodeType $nodeType */
                            $nodeType = $wrappedNodeType->getObject();
                            return $nodeType->hasConfiguration($args['configurationPath']);
                        }
                    ],
                    'configuration' => [
                        'type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class),
                        'description' => 'The configuration option with the specified configurationPath or NULL if it does not exist',
                        'args' => [
                            'configurationPath' => ['type' => Type::nonNull(Type::string())],
                        ],
                        'resolve' => static function (AccessibleObject $wrappedNodeType, array $args) {
                            /** @var CRNodeType $nodeType */
                            $nodeType = $wrappedNodeType->getObject();
                            return $nodeType->getConfiguration($args['configurationPath']);
                        }
                    ],
                    'fullConfiguration' => [
                        'type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class),
                        'deprecationReason' => 'Not part of the public API',
                        'description' => 'Get the full configuration of the node type. Should only be used internally.'
                    ],
                    'label' => ['type' => Type::string(), 'description' => 'The human-readable label of this node type'],
                    'options' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'description' => 'Additional options (if specified)'],
                    'properties' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'deprecationReason' => 'Not part of the public API', 'description' => 'A list of all defined properties of this node type'],
                    'propertyType' => [
                        'type' => Type::string(),
                        'deprecationReason' => 'Not part of the public API',
                        'description' => 'The configured type of the specified property',
                        'args' => [
                            'propertyName' => ['type' => Type::nonNull(Type::string())],
                        ],
                        'resolve' => static function (AccessibleObject $wrappedNodeType, array $args) {
                            /** @var CRNodeType $nodeType */
                            $nodeType = $wrappedNodeType->getObject();
                            return $nodeType->getPropertyType($args['propertyName']);
                        }
                    ],
                    'defaultValuesForProperties' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'description' => 'A list of the defined default values for each property, if any'],
                    'autoCreatedChildNodes' => [
                        'type' => Type::listOf($typeResolver->get(NodeNameAndType::class)),
                        'description' => 'A list of child nodes which should be automatically created',
                        'resolve' => static function (AccessibleObject $wrappedNodeType) {
                            /** @var CRNodeType $nodeType */
                            $nodeType = $wrappedNodeType->getObject();
                            $result = [];
                            foreach ($nodeType->getAutoCreatedChildNodes() as $nodeName => $nodeType) {
                                $result[] = ['nodeName' => $nodeName, 'nodeType' => new AccessibleObject($nodeType)];
                            }
                            return $result;
                        }
                    ],
                    'subNodeTypes' => [
                        'type' => Type::listOf($typeResolver->get(NodeType::class)),
                        'description' => 'All sub types of this node type',
                        'args' => [
                            'includeAbstractNodeTypes' => ['type' => Type::boolean(), 'description' => 'Whether to include abstract node types, defaults to TRUE'],
                        ],
                        'resolve' => function (AccessibleObject $wrappedNodeType, array $args) {
                            /** @var CRNodeType $nodeType */
                            $nodeType = $wrappedNodeType->getObject();
                            $includeAbstractNodeTypes = $args['includeAbstractNodeTypes'] ?? true;
                            return new IterableAccessibleObject($this->nodeTypeManager->getSubNodeTypes($nodeType->getName(), $includeAbstractNodeTypes));
                        }
                    ],
                ];
            }
        ]);
    }
}
