<?php
/**
 * Company Status Source Model
 */
namespace Zeltero\B2B\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Zeltero\B2B\Model\Company;

class CompanyStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Company::STATUS_PENDING, 'label' => __('Pending')],
            ['value' => Company::STATUS_APPROVED, 'label' => __('Approved')],
            ['value' => Company::STATUS_REJECTED, 'label' => __('Rejected')]
        ];
    }
}
