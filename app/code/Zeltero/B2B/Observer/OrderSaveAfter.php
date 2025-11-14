<?php
/**
 * Order Save Observer - Export to ERP
 */
namespace Zeltero\B2B\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zeltero\B2B\Service\ErpExport;

class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var ErpExport
     */
    protected $erpExport;

    /**
     * @param ErpExport $erpExport
     */
    public function __construct(ErpExport $erpExport)
    {
        $this->erpExport = $erpExport;
    }

    /**
     * Export order to ERP after save
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        
        if ($order && $order->getId()) {
            $this->erpExport->exportOrder($order);
        }
    }
}
