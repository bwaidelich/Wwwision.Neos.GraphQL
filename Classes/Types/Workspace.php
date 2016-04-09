<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class Workspace extends ObjectType
{

    public function __construct()
    {
        return parent::__construct([
            'name' => 'Workspace',
            'fields' => [
                'name' => ['type' => Type::string()],
            ],
        ]);
    }
}