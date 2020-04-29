<?php
namespace Wwwision\Neos\GraphQL\Types\Scalars;

use Neos\Media\Domain\Model\ResourceBasedInterface;
use Neos\ContentRepository\Domain\Model\NodeInterface;

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
        if (!\is_array($value)) {
            return $value;
        }
        array_walk_recursive($value, static function(&$item) {
            if (!\is_object($item)) {
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
