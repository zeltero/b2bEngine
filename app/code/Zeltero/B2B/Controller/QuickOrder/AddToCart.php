<?php
/**
 * Quick Order Add to Cart Controller
 */
namespace Zeltero\B2B\Controller\QuickOrder;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;

class AddToCart extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Cart $cart
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        Cart $cart,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
    }

    /**
     * Add products to cart from quick order
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        if (!$this->customerSession->isLoggedIn()) {
            return $result->setData([
                'success' => false,
                'message' => __('Please log in to use Quick Order.')
            ]);
        }

        try {
            $items = $this->getRequest()->getParam('items', []);
            $addedCount = 0;
            $errors = [];

            foreach ($items as $item) {
                try {
                    $sku = $item['sku'] ?? null;
                    $qty = $item['qty'] ?? 1;

                    if (!$sku) {
                        continue;
                    }

                    $product = $this->productRepository->get($sku);
                    
                    if (!$product->getId()) {
                        $errors[] = __('Product with SKU "%1" not found.', $sku);
                        continue;
                    }

                    $this->cart->addProduct($product, $qty);
                    $addedCount++;

                } catch (\Exception $e) {
                    $errors[] = __('Error adding SKU "%1": %2', $sku, $e->getMessage());
                }
            }

            $this->cart->save();

            $message = __('%1 product(s) added to cart.', $addedCount);
            if (!empty($errors)) {
                $message .= ' ' . implode(' ', $errors);
            }

            return $result->setData([
                'success' => $addedCount > 0,
                'message' => $message,
                'added_count' => $addedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => __('An error occurred: %1', $e->getMessage())
            ]);
        }
    }
}
