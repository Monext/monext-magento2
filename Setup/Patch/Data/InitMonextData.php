<?php
namespace Monext\Payline\Setup\Patch\Data;

use Magento\Customer\Model\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;
use Monext\Payline\Helper\Constants as HelperConstants;

/**
 */
class InitMonextData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var AttributeFactory
     */
    private $customerAttributeFactory;

    /**
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        AttributeFactory $customerAttributeFactory,
        SetFactory $attributeSetFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerAttributeFactory = $customerAttributeFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();

        $sales_order_status_state = array(
            array('status' => HelperConstants::ORDER_STATUS_PAYLINE_WAITING_ACCEPTANCE,'state' => 'processing','is_default' => '0','visible_on_front' => '1','label' => 'Payline Waiting Acceptance'),
            array('status' => HelperConstants::ORDER_STATUS_PAYLINE_WAITING_CAPTURE,'state' => 'processing','is_default' => '0','visible_on_front' => '1','label' => 'Payline Waiting Capture'),
            array('status' => HelperConstants::ORDER_STATUS_PAYLINE_CAPTURED,'state' => 'processing','is_default' => '0','visible_on_front' => '1','label' => 'Payline Captured'),
            array('status' => HelperConstants::ORDER_STATUS_PAYLINE_PENDING,'state' => 'new','is_default' => '0','visible_on_front' => '1','label' => 'Payline Pending'),
            array('status' => HelperConstants::ORDER_STATUS_PAYLINE_REFUSED,'state' => 'canceled','is_default' => '0','visible_on_front' => '1','label' => 'Payline Refused'),
            array('status' => HelperConstants::ORDER_STATUS_PAYLINE_ABANDONED,'state' => 'canceled','is_default' => '0','visible_on_front' => '1','label' => 'Payline Abandoned'),
            array('status' => HelperConstants::ORDER_STATUS_PAYLINE_FRAUD,'state' => 'canceled','is_default' => '0','visible_on_front' => '0','label' => 'Payline Fraud'),
            array('status' => HelperConstants::ORDER_STATUS_PAYLINE_CANCELED,'state' => 'canceled','is_default' => '0','visible_on_front' => '1','label' => 'Payline Canceled'),
            array('status' => HelperConstants::ORDER_STATUS_PAYLINE_PENDING_ONEY,'state' => Order::STATE_PENDING_PAYMENT,'is_default' => '0','visible_on_front' => '1','label' => 'Payline awaiting acceptance by Oney'),
        );


        $connection->startSetup();

        $data = [];
        foreach ($sales_order_status_state as $info) {
            $data[] = ['status' => $info['status'], 'label' => $info['label']];
        }

        $connection->insertArray(
            $this->moduleDataSetup->getTable('sales_order_status'),
            ['status', 'label'],
            $data,
            AdapterInterface::REPLACE
        );

            $data = [];
        foreach ($sales_order_status_state as $info) {
            $data[] = ['status' => $info['status'], 'state' =>$info['state'], 'default' => $info['is_default'], 'visible_on_front' => $info['visible_on_front']];
        }

        $connection->insertArray(
            $this->moduleDataSetup->getTable('sales_order_status_state'),
            ['status', 'state', 'is_default', 'visible_on_front'],
            $data,
            AdapterInterface::REPLACE
        );


        /** @var \Magento\Customer\Model\Attribute $attribute */
        $attribute = $this->customerAttributeFactory->create();
        $walletAttribute = $attribute->getIdByCode(\Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'wallet_id');
        if (!$walletAttribute) {
            $attribute->setData(array(
                'entity_type_id' => \Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => 'wallet_id',
                'type' => 'static',
                'frontend_label' => 'Wallet Id',
                'frontend_input' => 'text',
                'sort_order' => 200,
                'position' => 200,
                'is_user_defined' => 1,
                'is_unique' => 1,
                'is_visible' => 1,
            ));

            $attribute->save();

            $data = array(
                array('form_code' => 'adminhtml_customer', 'attribute_id' => $attribute->getId())
            );

            $connection
                ->insertMultiple($this->moduleDataSetup->getTable('customer_form_attribute'), $data);

            $attributeSet = $this->attributeSetFactory->create()->load(\Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER);

            $attribute
                ->setAttributeGroupId($attributeSet->getDefaultGroupId())
                ->setAttributeSetId($attributeSet->getId())
                ->setEntityTypeId(\Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER)
                ->save();
        }



        $attribute = $this->customerAttributeFactory->create();
        $categoryMapping = $attribute->getIdByCode(\Magento\Catalog\Model\Category::ENTITY, 'payline_category_mapping');
        if(!$categoryMapping) {
            /** @var  \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'payline_category_mapping', [
                'type'         => 'int',
                'label'        => 'Payline Category Mapping',
                'input'        => 'select',
                'source'       => 'Monext\Payline\Model\Category\Attribute\Source\CategoryMapping',
                'visible'      => true,
                'global'       => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'group'        => 'Content',
                'sort_order'   => 2000,
                'required'     => false,
                'user_defined' => true,
            ]);
        }

        $connection->endSetup();
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
