<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class NodeType extends ObjectType
{

    public function __construct()
    {
        return parent::__construct([
            'name' => 'NodeType',
            'fields' => [
                'name' => [
                    'type' => Type::string()
                ],
                'abstract' => [
                    'type' => Type::boolean()
                ],
                'final' => [
                    'type' => Type::boolean()
                ],
            ],
        ]);
    }
}