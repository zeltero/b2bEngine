<?php
/**
 * Admin Company Approve Controller
 */
namespace Zeltero\B2B\Controller\Adminhtml\Company;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Zeltero\B2B\Model\CompanyFactory;

class Approve extends Action
{
    const ADMIN_RESOURCE = 'Zeltero_B2B::company';

    /**
     * @var CompanyFactory
     */
    protected $companyFactory;

    /**
     * @param Context $context
     * @param CompanyFactory $companyFactory
     */
    public function __construct(
        Context $context,
        CompanyFactory $companyFactory
    ) {
        parent::__construct($context);
        $this->companyFactory = $companyFactory;
    }

    /**
     * Approve company
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('Invalid company ID.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $company = $this->companyFactory->create()->load($id);
            
            if (!$company->getId()) {
                $this->messageManager->addErrorMessage(__('Company not found.'));
                return $resultRedirect->setPath('*/*/');
            }

            $company->approve()->save();
            $this->messageManager->addSuccessMessage(__('Company has been approved.'));

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error approving company: %1', $e->getMessage()));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
