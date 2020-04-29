<?php
namespace Wwwision\Neos\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * A GraphQL type definition used to wrap mutation responses
 *
 * Note: Currently this type is not very useful, but we use it so that we can introduce async mutations at some point without breaking the type system
 */
class MutationResult extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'name' => 'MutationResult',
            'description' => 'The result of a mutation',
            'fields' => [
                'success' => ['type' => Type::boolean(), 'description' => 'Whether or not the mutation was successful'],
            ],
        ]);
    }
}
