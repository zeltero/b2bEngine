<?php
/**
 * Company Grid Actions Column
 */
namespace Zeltero\B2B\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class CompanyActions extends Column
{
    const URL_PATH_APPROVE = 'zeltero_b2b/company/approve';
    const URL_PATH_EDIT = 'zeltero_b2b/company/edit';
    const URL_PATH_DELETE = 'zeltero_b2b/company/delete';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['company_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                ['id' => $item['company_id']]
                            ),
                            'label' => __('Edit')
                        ]
                    ];
                    
                    if (isset($item['status']) && $item['status'] === 'pending') {
                        $item[$this->getData('name')]['approve'] = [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_APPROVE,
                                ['id' => $item['company_id']]
                            ),
                            'label' => __('Approve'),
                            'confirm' => [
                                'title' => __('Approve Company'),
                                'message' => __('Are you sure you want to approve this company?')
                            ]
                        ];
                    }
                    
                    $item[$this->getData('name')]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_DELETE,
                            ['id' => $item['company_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete Company'),
                            'message' => __('Are you sure you want to delete this company?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
