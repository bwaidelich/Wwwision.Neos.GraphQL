<?php
namespace Wwwision\Neos\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\Workspace as CRWorkspace;
use Neos\ContentRepository\Domain\Service\PublishingServiceInterface;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;

/**
 * A GraphQL type definition describing a Neos\ContentRepository\Domain\Model\Workspace
 */
class Workspace extends ObjectType
{

    /**
     * @Flow\Inject
     * @var PublishingServiceInterface
     */
    protected $publishingService;

    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'Workspace',
            'description' => 'A workspace',
            'fields' => [
                'name' => ['type' => Type::string(), 'description' => 'The name of this workspace'],
                'title' => ['type' => Type::string(), 'description' => 'The workspace title'],
                'description' => ['type' => Type::string(), 'description' => 'The workspace description'],
                'isPersonalWorkspace' => ['type' => Type::boolean(), 'description' => 'Whether this workspace is a user\'s personal workspace'],
                'isPrivateWorkspace' => ['type' => Type::boolean(), 'description' => 'Whether this workspace is shared only across users with access to internal workspaces, for example "reviewers"'],
                'isInternalWorkspace' => ['type' => Type::boolean(), 'description' => 'Whether this workspace is shared across all editors'],
                'isPublicWorkspace' => ['type' => Type::boolean(), 'description' => 'Whether this workspace is public to everyone, even without authentication'],
                'baseWorkspace' => ['type' => $typeResolver->get(Workspace::class), 'description' => 'The base workspace, if any'],
                'baseWorkspaces' => ['type' => Type::listOf($typeResolver->get(Workspace::class)), 'description' => 'All base workspaces, if any'],
                'nodeCount' => ['type' => Type::int(), 'description' => 'The number of nodes in this workspace'],
                'unpublishedNodes' => [
                    'type' => Type::listOf($typeResolver->get(Node::class)),
                    'resolve' => function (AccessibleObject $wrappedWorkspace) {
                        /** @var CRWorkspace $workspace */
                        $workspace = $wrappedWorkspace->getObject();
                        return new IterableAccessibleObject($this->publishingService->getUnpublishedNodes($workspace));
                    }
                ],
                'unpublishedNodesCount' => [
                    'type' => Type::int(),
                    'resolve' => function (AccessibleObject $wrappedWorkspace) {
                        /** @var CRWorkspace $workspace */
                        $workspace = $wrappedWorkspace->getObject();
                        return $this->publishingService->getUnpublishedNodesCount($workspace);
                    }
                ],
            ],
        ]);
    }
}