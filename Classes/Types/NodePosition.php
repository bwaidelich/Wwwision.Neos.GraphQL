<?php
namespace Wwwision\Neos\GraphQl\Types;

use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;

class NodePosition extends EnumType
{

    public function __construct()
    {
        return parent::__construct([
            'name' => 'NodePosition',
            'values' => [
                'BEFORE' => ['value' => 'before'],
                'INTO' => ['value' => 'into'],
                'AFTER' => ['value' => 'after']
            ]
        ]);
    }
}