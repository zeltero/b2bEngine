<?php
/**
 * Admin RFQ Index Controller
 */
namespace Zeltero\B2B\Controller\Adminhtml\Rfq;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Zeltero_B2B::rfq';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * RFQ grid page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Zeltero_B2B::rfq');
        $resultPage->getConfig()->getTitle()->prepend(__('Requests for Quote'));
        return $resultPage;
    }
}
