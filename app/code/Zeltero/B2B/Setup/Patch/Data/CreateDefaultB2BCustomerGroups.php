<?php
/**
 * Initial Setup Data Patch
 * Creates default customer groups for B2B
 */
namespace Zeltero\B2B\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Tax\Model\ClassModel;

class CreateDefaultB2BCustomerGroups implements DataPatchInterface
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var GroupInterfaceFactory
     */
    private $groupFactory;

    /**
     * @var ClassModel
     */
    private $taxClass;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupInterfaceFactory $groupFactory
     * @param ClassModel $taxClass
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupFactory,
        ClassModel $taxClass
    ) {
        $this->groupRepository = $groupRepository;
        $this->groupFactory = $groupFactory;
        $this->taxClass = $taxClass;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $groups = [
            [
                'code' => 'b2b_wholesale_bronze',
                'tax_class_name' => 'Retail Customer'
            ],
            [
                'code' => 'b2b_wholesale_silver',
                'tax_class_name' => 'Retail Customer'
            ],
            [
                'code' => 'b2b_wholesale_gold',
                'tax_class_name' => 'Retail Customer'
            ]
        ];

        foreach ($groups as $groupData) {
            try {
                // Check if group already exists
                $searchResults = $this->groupRepository->getList(
                    $this->createSearchCriteria('customer_group_code', $groupData['code'])
                );

                if ($searchResults->getTotalCount() > 0) {
                    continue; // Group already exists
                }

                // Get tax class ID
                $taxClassId = $this->getTaxClassId($groupData['tax_class_name']);

                // Create new group
                $group = $this->groupFactory->create();
                $group->setCode(ucwords(str_replace('_', ' ', $groupData['code'])));
                $group->setTaxClassId($taxClassId);
                
                $this->groupRepository->save($group);

            } catch (\Exception $e) {
                // Group creation failed, continue with next
                continue;
            }
        }

        return $this;
    }

    /**
     * Get tax class ID by name
     *
     * @param string $taxClassName
     * @return int
     */
    private function getTaxClassId($taxClassName)
    {
        $taxClass = $this->taxClass->getCollection()
            ->addFieldToFilter('class_name', $taxClassName)
            ->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->getFirstItem();

        return $taxClass->getId() ?: 3; // Default to 3 (Retail Customer)
    }

    /**
     * Create search criteria
     *
     * @param string $field
     * @param string $value
     * @return \Magento\Framework\Api\SearchCriteriaInterface
     */
    private function createSearchCriteria($field, $value)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $searchCriteriaBuilder = $objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        
        return $searchCriteriaBuilder
            ->addFilter($field, $value)
            ->create();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
