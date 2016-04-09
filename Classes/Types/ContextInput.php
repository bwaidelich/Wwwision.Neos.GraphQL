<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Wwwision\Neos\GraphQl\TypeResolver;

class ContextInput extends InputObjectType
{

    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'ContextInput',
            'fields' => [
                'workspaceName' => ['type' => Type::string()],
                'currentDateTime' => ['type' => $typeResolver->get(DateTime::class)],
                'dimensions' => ['type' => $typeResolver->get(Dimensions::class)],
                'targetDimensions' => ['type' => Type::string()],
                'invisibleContentShown' => ['type' => Type::boolean()],
                'removedContentShown' => ['type' => Type::boolean()],
                'inaccessibleContentShown' => ['type' => Type::boolean()],
                'currentSite' => ['type' => Type::string()],
                'currentDomain' => ['type' => Type::string()],
            ],
        ]);
    }
}