<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use TYPO3\TYPO3CR\Domain\Service\Context as TYPO3CRContext;
use Wwwision\Neos\GraphQl\TypeResolver;
use Wwwision\Neos\GraphQl\Types\Wrapper\AccessibleObject;
use Wwwision\Neos\GraphQl\Types\Wrapper\IterableAccessibleObject;

class Context extends ObjectType
{

    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'Context',
            'fields' => [
                'workspace' => [
                    'type' => $typeResolver->get(Workspace::class),
                    'resolve' => function (AccessibleObject $wrappedNode) {
                        // NOTE: Context::getWorkspace() implicitly create the workspace by default, that's why we have to override this method here!
                        /** @var TYPO3CRContext $context */
                        $context = $wrappedNode->getObject();
                        return new AccessibleObject($context->getWorkspace(false));
                    }
                ],
                'workspaceName' => ['type' => Type::string()],
                'currentDateTime' => ['type' => $typeResolver->get(DateTime::class)],
                'rootNode' => ['type' => $typeResolver->get(Node::class)],
                'node' => [
                    'type' => $typeResolver->get(Node::class),
                    'args' => [
                        'identifier' => ['type' => $typeResolver->get(NodeIdentifier::class)],
                        'path' => ['type' => $typeResolver->get(AbsoluteNodePath::class)],
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
                    'args' => [
                        'identifier' => ['type' => Type::nonNull($typeResolver->get(NodeIdentifier::class))],
                    ],
                    'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                        /** @var TYPO3CRContext $context */
                        $context = $wrappedNode->getObject();
                        return new IterableAccessibleObject($context->getNodeVariantsByIdentifier($args['identifier']));
                    }
                ],
               'nodesOnPath' => [
                    'type' => Type::listOf($typeResolver->get(Node::class)),
                    'args' => [
                        'start' => ['type' => Type::nonNull($typeResolver->get(AbsoluteNodePath::class))],
                        'end' => ['type' => Type::nonNull($typeResolver->get(AbsoluteNodePath::class))],
                    ],
                    'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                        /** @var TYPO3CRContext $context */
                        $context = $wrappedNode->getObject();

                        return new IterableAccessibleObject($context->getNodesOnPath($args['start'], $args['end']));
                    }
                ],
                'invisibleContentShown' => ['type' => Type::boolean()],
                'removedContentShown' => ['type' => Type::boolean()],
                'inaccessibleContentShown' => ['type' => Type::boolean()],
                'dimensions' => ['type' => $typeResolver->get(Dimensions::class)],
            ],
        ]);
    }
}