<?php

namespace Monext\Payline\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;

class UpdateCategoryMapping implements DataPatchInterface
{
    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    private  $customerAttributeFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private  $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Customer\Model\AttributeFactory $customerAttributeFactory
     */
    public function __construct(\Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
                                \Magento\Customer\Model\AttributeFactory $customerAttributeFactory)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerAttributeFactory = $customerAttributeFactory;
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

    public function apply()
    {

        $connection = $this->moduleDataSetup->getConnection();
        $attribute = $this->customerAttributeFactory->create();
        $categoryMapping = $attribute->getIdByCode(\Magento\Catalog\Model\Category::ENTITY, 'payline_category_mapping');

        if($categoryMapping) {
            $connection->update('catalog_category_entity_int',
                ['value' => new \Zend_Db_Expr("CASE
                           WHEN substring(value, 1,2) = '10' THEN 1
                           WHEN substring(value, 1,2) = '20' THEN 2
                           WHEN substring(value, 1,2) = '40' THEN 4
                           WHEN substring(value, 1,2) = '50' THEN 5
                           WHEN substring(value, 1,2) = '59' THEN 5
                           WHEN substring(value, 1,2) = '11' THEN 11
                           WHEN substring(value, 1,2) = '12' THEN 12
                           WHEN substring(value, 1,2) = '17' THEN 17
                           WHEN substring(value, 1,2) = '24' THEN 24
                           ELSE null
                    END")],
                ['value > 26', 'attribute_id = '. $categoryMapping]
            );
        }


    }
}
