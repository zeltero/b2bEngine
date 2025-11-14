<?php
/**
 * ERP Export Service
 */
namespace Zeltero\B2B\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class ErpExport
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Filesystem $filesystem
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->filesystem = $filesystem;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Export order to ERP format
     *
     * @param Order $order
     * @return string|false File path on success, false on failure
     */
    public function exportOrder(Order $order)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $format = $this->getExportFormat();
        $exportPath = $this->getExportPath();

        $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $fullPath = $exportPath . '/' . $order->getIncrementId() . '.' . $format;

        try {
            $data = $this->prepareOrderData($order);
            
            switch ($format) {
                case 'csv':
                    $content = $this->generateCsv($data);
                    break;
                case 'xml':
                    $content = $this->generateXml($data);
                    break;
                case 'json':
                    $content = $this->generateJson($data);
                    break;
                default:
                    return false;
            }

            $varDirectory->writeFile($fullPath, $content);
            return $varDirectory->getAbsolutePath($fullPath);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Prepare order data for export
     *
     * @param Order $order
     * @return array
     */
    protected function prepareOrderData(Order $order)
    {
        $data = [
            'order_id' => $order->getIncrementId(),
            'customer_email' => $order->getCustomerEmail(),
            'customer_name' => $order->getCustomerName(),
            'order_date' => $order->getCreatedAt(),
            'status' => $order->getStatus(),
            'grand_total' => $order->getGrandTotal(),
            'currency' => $order->getOrderCurrencyCode(),
            'items' => []
        ];

        foreach ($order->getAllVisibleItems() as $item) {
            $data['items'][] = [
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'qty' => $item->getQtyOrdered(),
                'price' => $item->getPrice(),
                'row_total' => $item->getRowTotal()
            ];
        }

        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $data['billing_address'] = [
                'firstname' => $billingAddress->getFirstname(),
                'lastname' => $billingAddress->getLastname(),
                'street' => implode(', ', $billingAddress->getStreet()),
                'city' => $billingAddress->getCity(),
                'postcode' => $billingAddress->getPostcode(),
                'country' => $billingAddress->getCountryId(),
                'telephone' => $billingAddress->getTelephone()
            ];
        }

        return $data;
    }

    /**
     * Generate CSV content
     *
     * @param array $data
     * @return string
     */
    protected function generateCsv(array $data)
    {
        $csv = "Order ID,Customer Email,Customer Name,Order Date,Status,Total,Currency\n";
        $csv .= sprintf(
            '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
            $data['order_id'],
            $data['customer_email'],
            $data['customer_name'],
            $data['order_date'],
            $data['status'],
            $data['grand_total'],
            $data['currency']
        );
        
        $csv .= "\nOrder Items:\n";
        $csv .= "SKU,Name,Qty,Price,Row Total\n";
        
        foreach ($data['items'] as $item) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s"' . "\n",
                $item['sku'],
                $item['name'],
                $item['qty'],
                $item['price'],
                $item['row_total']
            );
        }
        
        return $csv;
    }

    /**
     * Generate XML content
     *
     * @param array $data
     * @return string
     */
    protected function generateXml(array $data)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><order></order>');
        
        $xml->addChild('order_id', $data['order_id']);
        $xml->addChild('customer_email', $data['customer_email']);
        $xml->addChild('customer_name', $data['customer_name']);
        $xml->addChild('order_date', $data['order_date']);
        $xml->addChild('status', $data['status']);
        $xml->addChild('grand_total', $data['grand_total']);
        $xml->addChild('currency', $data['currency']);
        
        $items = $xml->addChild('items');
        foreach ($data['items'] as $itemData) {
            $item = $items->addChild('item');
            foreach ($itemData as $key => $value) {
                $item->addChild($key, htmlspecialchars($value));
            }
        }
        
        return $xml->asXML();
    }

    /**
     * Generate JSON content
     *
     * @param array $data
     * @return string
     */
    protected function generateJson(array $data)
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Check if ERP export is enabled
     *
     * @return bool
     */
    protected function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'zeltero_b2b/erp/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get export format
     *
     * @return string
     */
    protected function getExportFormat()
    {
        return $this->scopeConfig->getValue(
            'zeltero_b2b/erp/export_format',
            ScopeInterface::SCOPE_STORE
        ) ?: 'csv';
    }

    /**
     * Get export path
     *
     * @return string
     */
    protected function getExportPath()
    {
        return $this->scopeConfig->getValue(
            'zeltero_b2b/erp/export_path',
            ScopeInterface::SCOPE_STORE
        ) ?: 'b2b/erp/export';
    }
}
