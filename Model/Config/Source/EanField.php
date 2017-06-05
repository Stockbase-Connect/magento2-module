<?php

namespace Stockbase\Integration\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Option\ArrayInterface;

/**
 * Field selector source for the EAN field select.
 */
class EanField implements ArrayInterface
{
    private $objectManager;

    /**
     * EanField constructor.
     * @param ObjectManagerInterface $interface
     */
    public function __construct(ObjectManagerInterface $interface)
    {
        $this->objectManager = $interface;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        // Load the ResourceModel with all the Attribute Collections (attribute sets in magento)
        $collection = $this->objectManager->get(Collection::class);

        // Filter on the Catalog_Product KEY_ENTITY_TYPE_ID which is 4 for products
        $collection->addFieldToFilter(Set::KEY_ENTITY_TYPE_ID, 4);

        // Load the items with the given filter (so all product attributes will be filtered)
        $attributes = $collection->load()->getItems();

        // Empty array for the option array to return for the configuration source model in stockbase settings
        $optionArray = [];
        $optionArray[] = ['label' => '', 'value' => ''];
        foreach ($attributes as $attribute) {
            $optionArray[] = [
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributeCode(),
            ];
        }

        return $optionArray;
    }
}
