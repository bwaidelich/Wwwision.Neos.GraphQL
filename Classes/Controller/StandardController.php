<?php
namespace Wwwision\Neos\GraphQl\Controller;

use GraphQL\GraphQL;
use GraphQL\Schema;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use Wwwision\Neos\GraphQl\TypeResolver;
use Wwwision\Neos\GraphQl\Types\Mutation;
use Wwwision\Neos\GraphQl\Types\RootQuery;

/**
 * Default controller serving a GraphiQL interface as well as the GraphQL endpoint
 */
class StandardController extends ActionController
{

    /**
     * @Flow\Inject
     * @var TypeResolver
     */
    protected $typeResolver;

    /**
     * @var array
     */
    protected $supportedMediaTypes = ['application/json', 'text/html'];

    /**
     * @return void
     */
    public function indexAction()
    {
    }

    /**
     * @param string $query
     * @param string $variables
     * @param string $operation
     * @return string
     * @Flow\SkipCsrfProtection
     */
    public function queryAction($query, $variables = null, $operation = null)
    {
        $schema = new Schema($this->typeResolver->get(RootQuery::class), $this->typeResolver->get(Mutation::class));
        $decodedVariables = json_decode($variables, true);
        try {
            $result = GraphQL::execute($schema, $query, null, $decodedVariables , $operation);
        } catch (\Exception $exception) {
            $result = ['errors' => [['message' => $exception->getMessage()]]];
        }
        header('Content-Type: application/json');
        return json_encode($result);
    }

}