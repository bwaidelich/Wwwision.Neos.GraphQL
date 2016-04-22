<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Wwwision\Neos\GraphQl\TypeResolver;

/**
 * A GraphQL type definition wrapping a node name and its type
 */
class NodeNameAndType extends ObjectType
{
    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'NodeNameAndType',
            'description' => 'A wrapper type for node name and node type, used by nodeType.autoCreatedChildNodes for example',
            'fields' => [
                'nodeName' => ['type' => Type::string(), 'description' => 'Name of the node'],
                'nodeType' => ['type' => $typeResolver->get(NodeType::class), 'description' => 'Type of the node'],
            ],
        ]);
    }
}