<?php
/**
 * Company Collection
 */
namespace Zeltero\B2B\Model\ResourceModel\Company;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'company_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Zeltero\B2B\Model\Company::class,
            \Zeltero\B2B\Model\ResourceModel\Company::class
        );
    }
}
