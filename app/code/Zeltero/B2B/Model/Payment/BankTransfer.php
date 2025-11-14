<?php
/**
 * Bank Transfer Payment Method
 */
namespace Zeltero\B2B\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;

class BankTransfer extends AbstractMethod
{
    const CODE = 'zeltero_banktransfer';

    protected $_code = self::CODE;
    protected $_isOffline = true;

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote) && $this->getConfigData('active');
    }
}
