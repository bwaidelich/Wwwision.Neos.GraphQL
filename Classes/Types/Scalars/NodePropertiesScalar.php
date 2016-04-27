<?php
namespace Wwwision\Neos\GraphQL\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Media\Domain\Model\ResourceBasedInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use Wwwision\GraphQL\IterableAccessibleObject;

/**
 * Type scalar for unknown structures (represented as JSON object)
 */
class NodePropertiesScalar extends UnstructuredObjectScalar
{
    /**
     * @var string
     */
    public $name = 'NodePropertiesScalar';

    /**
     * @var string
     */
    public $description = 'Type scalar for node properties';

    /**
     * @param array $value
     * @return array
     */
    public function serialize($value)
    {
        $value = parent::serialize($value);
        if (!is_array($value)) {
            return $value;
        }
        array_walk_recursive($value, function(&$item) {
            if (!is_object($item)) {
                return;
            }
            if ($item instanceof \DateTimeInterface) {
                $item = $item->format(DATE_ISO8601);
            } elseif ($item instanceof ResourceBasedInterface) {
                $item = $item->getResource()->getSha1();
            } elseif ($item instanceof NodeInterface) {
                $item = $item->getIdentifier();
            }
        });
        return $value;
    }
}