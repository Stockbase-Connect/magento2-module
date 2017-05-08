<?php

namespace Strategery\Stockbase\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

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
            ['value' => self::PRODUCTION, 'label' => __('Production')],
            ['value' => self::STAGING, 'label' => __('Staging')],
        ];
    }
}
