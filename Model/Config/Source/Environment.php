<?php

namespace Strategery\Stockbase\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Selector source for the Environment configuration option.
 */
class Environment implements ArrayInterface
{
    const PRODUCTION = 'production';
    const STAGING = 'staging';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STAGING, 'label' => __('Staging')],
            ['value' => self::PRODUCTION, 'label' => __('Production')],
        ];
    }
}
