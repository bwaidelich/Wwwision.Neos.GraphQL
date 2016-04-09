<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Service\NodeOperations;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use Wwwision\Neos\GraphQl\TypeResolver;
use Wwwision\Neos\GraphQl\Types\Wrapper\AccessibleObject;

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
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'Mutations',
            'fields' => [
                'hideNode' => [
                    'type' => $typeResolver->get(Node::class),
                    'args' => [
                        'context' => ['type' => $typeResolver->get(ContextInput::class)],
                        'node' => ['type' => Type::nonNull(Type::string())],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);

                        $node = NodeIdentifier::isNodeIdentifier($args['node']) ? $context->getNodeByIdentifier($args['node']) : $context->getNode($args['node']);
                        if ($node === null) {
                            throw new \InvalidArgumentException(sprintf('The node "%s" could not be found', $args['node']), 1460046627);
                        }
                        $node->setHidden(true);

                        return new AccessibleObject($node);
                    },
                ],
                'moveNode' => [
                    'type' => $typeResolver->get(Node::class),
                    'args' => [
                        'context' => ['type' => Type::nonNull($typeResolver->get(ContextInput::class))],
                        'node' => ['type' => Type::nonNull(Type::string())],
                        'targetNode' => ['type' => Type::nonNull(Type::string())],
                        'position' => ['type' => Type::nonNull($typeResolver->get(NodePosition::class))],
                    ],
                    'resolve' => function ($_, $args) {
                        $context = $this->contextFactory->create($args['context']);

                        $node = NodeIdentifier::isNodeIdentifier($args['node']) ? $context->getNodeByIdentifier($args['node']) : $context->getNode($args['node']);
                        if ($node === null) {
                            throw new \InvalidArgumentException(sprintf('The node "%s" could not be found', $args['node']), 1460046627);
                        }
                        $targetNode = NodeIdentifier::isNodeIdentifier($args['targetNode']) ? $context->getNodeByIdentifier($args['targetNode']) : $context->getNode($args['targetNode']);
                        if ($targetNode === null) {
                            throw new \InvalidArgumentException(sprintf('The targetNode "%s" could not be found', $args['node']), 1460046630);
                        }
                        $this->nodeOperations->move($node, $targetNode, $args['position']);

                        return new AccessibleObject($node);
                    },
                ],
            ]
        ]);
    }
}