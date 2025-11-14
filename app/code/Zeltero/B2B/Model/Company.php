<?php
/**
 * Company Model
 */
namespace Zeltero\B2B\Model;

use Magento\Framework\Model\AbstractModel;

class Company extends AbstractModel
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Zeltero\B2B\Model\ResourceModel\Company::class);
    }

    /**
     * Get company ID
     *
     * @return int|null
     */
    public function getCompanyId()
    {
        return $this->getData('company_id');
    }

    /**
     * Get company name
     *
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->getData('company_name');
    }

    /**
     * Set company name
     *
     * @param string $name
     * @return $this
     */
    public function setCompanyName($name)
    {
        return $this->setData('company_name', $name);
    }

    /**
     * Get approval status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData('status');
    }

    /**
     * Set approval status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }

    /**
     * Check if company is approved
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->getStatus() === self::STATUS_APPROVED;
    }

    /**
     * Approve company
     *
     * @return $this
     */
    public function approve()
    {
        return $this->setStatus(self::STATUS_APPROVED);
    }

    /**
     * Reject company
     *
     * @return $this
     */
    public function reject()
    {
        return $this->setStatus(self::STATUS_REJECTED);
    }
}
