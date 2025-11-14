<?php
/**
 * Company Registration Submit Controller
 */
namespace Zeltero\B2B\Controller\Company;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Zeltero\B2B\Model\CompanyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Register extends Action
{
    /**
     * @var CompanyFactory
     */
    protected $companyFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param Context $context
     * @param CompanyFactory $companyFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Context $context,
        CompanyFactory $companyFactory,
        ScopeConfigInterface $scopeConfig,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->companyFactory = $companyFactory;
        $this->scopeConfig = $scopeConfig;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Process company registration
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if (!$this->getRequest()->isPost()) {
            return $resultRedirect->setPath('*/*/index');
        }

        try {
            $data = $this->getRequest()->getPostValue();
            
            $company = $this->companyFactory->create();
            $company->setData($data);
            
            // Check if auto-approve is enabled
            $autoApprove = $this->scopeConfig->getValue(
                'zeltero_b2b/company/auto_approve',
                ScopeInterface::SCOPE_STORE
            );
            
            if ($autoApprove) {
                $company->setStatus(\Zeltero\B2B\Model\Company::STATUS_APPROVED);
                $message = __('Your company has been registered and approved.');
            } else {
                $company->setStatus(\Zeltero\B2B\Model\Company::STATUS_PENDING);
                $message = __('Your company registration has been submitted for approval.');
            }
            
            $company->save();
            
            $this->messageManager->addSuccessMessage($message);
            
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your registration: %1', $e->getMessage())
            );
        }
        
        return $resultRedirect->setPath('*/*/index');
    }
}
