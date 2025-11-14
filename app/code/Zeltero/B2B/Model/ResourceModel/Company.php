<?php
/**
 * Company Resource Model
 */
namespace Zeltero\B2B\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Company extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('zeltero_b2b_company', 'company_id');
    }
}
