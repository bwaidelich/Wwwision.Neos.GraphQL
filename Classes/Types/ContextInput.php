<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Wwwision\Neos\GraphQl\TypeResolver;

/**
 * A GraphQL input type definition for a \TYPO3\TYPO3CR\Domain\Service\Context
 */
class ContextInput extends InputObjectType
{

    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'ContextInput',
            'description' => 'Input type for the TYPO3CR context',
            'fields' => [
                'workspaceName' => ['type' => Type::string(), 'description' => 'The workspace of this context, e.g. "live" or "user-admin"'],
                'currentDateTime' => ['type' => $typeResolver->get(DateTime::class), 'description' => 'Simulated date & time, defaults to the current server time'],
                'dimensions' => ['type' => $typeResolver->get(Dimensions::class), 'description' => 'Dimensions for this context, e.g. {"language": ["en", "de"]}'],
                'targetDimensions' => ['type' => Type::string(), 'description' => 'Dimensions to be applied for new/updated nodes, e.g. {"language": "en"}'],
                'invisibleContentShown' => ['type' => Type::boolean(), 'description' => 'Whether or not to show nodes with a "hidden" flag, defaults to FALSE'],
                'removedContentShown' => ['type' => Type::boolean(), 'description' => 'Whether or not to show nodes with a "removed" flag, defaults to FALSE'],
                'inaccessibleContentShown' => ['type' => Type::boolean(), 'description' => 'Whether or not to ignore node access restrictions, defaults to FALSE'],
                'currentSite' => ['type' => $typeResolver->get(Site::class)],
                'currentDomain' => ['type' => $typeResolver->get(Domain::class)],
            ],
        ]);
    }
}