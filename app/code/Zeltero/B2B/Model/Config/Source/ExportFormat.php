<?php
/**
 * Export Format Source Model
 */
namespace Zeltero\B2B\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ExportFormat implements OptionSourceInterface
{
    const FORMAT_CSV = 'csv';
    const FORMAT_XML = 'xml';
    const FORMAT_JSON = 'json';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FORMAT_CSV, 'label' => __('CSV')],
            ['value' => self::FORMAT_XML, 'label' => __('XML')],
            ['value' => self::FORMAT_JSON, 'label' => __('JSON')]
        ];
    }
}
