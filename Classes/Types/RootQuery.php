<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use Wwwision\Neos\GraphQl\TypeResolver;
use Wwwision\Neos\GraphQl\Types\Wrapper\AccessibleObject;
use Wwwision\Neos\GraphQl\Types\Wrapper\IterableAccessibleObject;

class RootQuery extends ObjectType
{

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'Query',
            'fields' => [
                'context' => [
                    'type' => $typeResolver->get(Context::class),
                    'args' => [
                        'workspaceName' => ['type' => Type::string()],
                        'currentDateTime' => ['type' => $typeResolver->get(DateTime::class)],
                        'dimensions' => ['type' => $typeResolver->get(Dimensions::class)],
                        'targetDimensions' => ['type' => Type::string()],
                        'invisibleContentShown' => ['type' => Type::boolean()],
                        'removedContentShown' => ['type' => Type::boolean()],
                        'inaccessibleContentShown' => ['type' => Type::boolean()],
                        'currentSite' => ['type' => $typeResolver->get(Site::class)],
                        'currentDomain' => ['type' => $typeResolver->get(Domain::class)],
                    ],
                    'resolve' => function ($_, $contextConfiguration) {
                        $context = $this->contextFactory->create($contextConfiguration);
                        return new AccessibleObject($context);
                    },
                ],
                'node' => [
                    'type' => $typeResolver->get(Node::class),
                    'args' => [
                        'identifier' => ['type' => $typeResolver->get(NodeIdentifier::class)],
                        'path' => ['type' => $typeResolver->get(AbsoluteNodePath::class)],
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
                        'start' => ['type' => Type::nonNull($typeResolver->get(AbsoluteNodePath::class))],
                        'end' => ['type' => Type::nonNull($typeResolver->get(AbsoluteNodePath::class))],
                    ],
                    'resolve' => function ($_, array $args) {
                        $defaultContext = $this->contextFactory->create();

                        return new IterableAccessibleObject($defaultContext->getNodesOnPath($args['start'], $args['end']));
                    }
                ],
            ]
        ]);
    }
}