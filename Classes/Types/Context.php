<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use TYPO3\TYPO3CR\Domain\Service\Context as TYPO3CRContext;
use Wwwision\Neos\GraphQl\TypeResolver;
use Wwwision\Neos\GraphQl\Types\Wrapper\AccessibleObject;
use Wwwision\Neos\GraphQl\Types\Wrapper\IterableAccessibleObject;

/**
 * A GraphQL type definition describing a TYPO3\TYPO3CR\Domain\Service\Context
 */
class Context extends ObjectType
{

    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'Context',
            'description' => 'The context, including information about the current workspace, date and dimensions',
            'fields' => [
                'workspace' => [
                    'type' => $typeResolver->get(Workspace::class),
                    'description' => 'Workspace of this context',
                    'resolve' => function (AccessibleObject $wrappedNode) {
                        // NOTE: Context::getWorkspace() implicitly create the workspace by default, that's why we have to override this method here!
                        /** @var TYPO3CRContext $context */
                        $context = $wrappedNode->getObject();
                        return new AccessibleObject($context->getWorkspace(false));
                    }
                ],
                'workspaceName' => ['type' => Type::string(), 'description' => 'The name of the current workspace'],
                'currentDateTime' => ['type' => $typeResolver->get(Scalars\DateTime::class), 'description' => 'The current date and time, allowing for date/time simulation'],
                'rootNode' => ['type' => $typeResolver->get(Node::class), 'The root node for this context workspace'],
                'node' => [
                    'type' => $typeResolver->get(Node::class),
                    'description' => 'A node specified by its absolute path or identifier',
                    'args' => [
                        'identifier' => ['type' => $typeResolver->get(Scalars\NodeIdentifier::class), 'description' => 'The node identifier (not the technical persistence id)'],
                        'path' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class), 'description' => 'The absolute node path in the form "/sites/some-site/some/path"'],
                    ],
                    'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                        /** @var TYPO3CRContext $context */
                        $context = $wrappedNode->getObject();
                        if (isset($args['identifier'])) {
                            return new AccessibleObject($context->getNodeByIdentifier($args['identifier']));
                        } elseif (isset($args['path'])) {
                            return new AccessibleObject($context->getNode($args['path']));
                        }
                        throw new \InvalidArgumentException('node path or identifier have to be specified!', 1460064707);
                    }
                ],
                'nodeVariantsByIdentifier' => [
                    'type' => Type::listOf($typeResolver->get(Node::class)),
                    'deprecationReason' => 'Not part of the public API',
                    'description' => 'All node variants for the given node identifier',
                    'args' => [
                        'identifier' => ['type' => Type::nonNull($typeResolver->get(Scalars\NodeIdentifier::class))],
                    ],
                    'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                        /** @var TYPO3CRContext $context */
                        $context = $wrappedNode->getObject();
                        return new IterableAccessibleObject($context->getNodeVariantsByIdentifier($args['identifier']));
                    }
                ],
               'nodesOnPath' => [
                    'type' => Type::listOf($typeResolver->get(Node::class)),
                    'description' => 'Finds all nodes lying on the path specified by (and including) the given staring and end point',
                    'args' => [
                        'startingPoint' => ['type' => Type::nonNull($typeResolver->get(Scalars\AbsoluteNodePath::class)), 'description' => 'Either an absolute path or an actual node specifying the starting point, for example /sites/mysitecom'],
                        'endPoint' => ['type' => Type::nonNull($typeResolver->get(Scalars\AbsoluteNodePath::class)), 'description' => 'Either an absolute path or an actual node specifying the end point, for example /sites/mysitecom/homepage/subpage'],
                    ],
                    'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                        /** @var TYPO3CRContext $context */
                        $context = $wrappedNode->getObject();

                        return new IterableAccessibleObject($context->getNodesOnPath($args['startingPoint'], $args['endPoint']));
                    }
                ],
                'isInvisibleContentShown' => ['type' => Type::boolean(), 'description' => 'Whether nodes that are usually invisible are accessible in this context', 'resolve' => function (AccessibleObject $wrappedContext) { return $wrappedContext->getObject()->isInvisibleContentShown(); }],
                'isRemovedContentShown' => ['type' => Type::boolean(), 'description' => 'Whether nodes with a "removed" flag are accessible in this context', 'resolve' => function (AccessibleObject $wrappedContext) { return $wrappedContext->getObject()->isRemovedContentShown(); }],
                'isInaccessibleContentShown' => ['type' => Type::boolean(), 'description' => 'Whether nodes with access restrictions are accessible to everybody in this context', 'resolve' => function (AccessibleObject $wrappedContext) { return $wrappedContext->getObject()->isInaccessibleContentShown(); }],
                'dimensions' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'deprecationReason' => 'Not part of the public API', 'description' => 'A list of all defined dimensions, indexed by the dimension key'],
                'targetDimensions' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'deprecationReason' => 'Not part of the public API', 'description' => 'A flat list of all dimensions that should be applied on creation, indexed by the dimension key'],
                'targetDimensionValues' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'deprecationReason' => 'Not part of the public API', 'description' => 'A list of all dimension values that should be applied on creation, indexed by the dimension key'],
            ],
        ]);
    }
}