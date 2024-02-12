<?php

namespace Monext\Payline\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Monext\Payline\Api\Data\OrderIncrementIdTokenInterface;
use Magento\Framework\DB\Ddl\Table;

class UpdgradeIncrementIdToken implements \Magento\Framework\Setup\Patch\SchemaPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private  $moduleDataSetup;


    public function __construct(\Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $connection = $this->moduleDataSetup->getConnection();

        $tableToken = $this->moduleDataSetup->getTable('payline_order_increment_id_token');

        $connection->dropForeignKey($connection->getIndexName(
            $tableToken,
            ['token', 'order_entity_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ));

        $connection->dropColumn($tableToken, 'cart_id');
        $connection->dropColumn($tableToken, 'order_entity_id');
        $connection->dropColumn($tableToken, 'sha');
        $connection->dropColumn($tableToken, 'state');
        $connection->dropColumn($tableToken, 'updated_at');
        $connection->dropColumn($tableToken, 'created_at');
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

        $tableToken = $this->moduleDataSetup->getTable('payline_order_increment_id_token');

         $connection->addColumn(
            $tableToken,
            'cart_id',
             [
                 'type' => Table::TYPE_INTEGER,
                 'comment' => 'Cart id',
                 'nullable' => true,
                 'default' => null,
                 'unsigned' => true
             ]
        );

        $connection->addColumn(
            $tableToken,
            'order_entity_id',
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Order id',
                'nullable' => true,
                'default' => null,
                'unsigned' => true
            ]
        );

        $connection->addColumn(
            $tableToken,
            'sha',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Cart SHA signature',
                'nullable' => true,
                'default' => null,
            ]
        );

        $connection->addColumn(
            $tableToken,
            'state',
            [
                'comment' => 'Token state',
                'type' => Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => null,
                'unsigned' => true
            ]
        );

        $connection->addColumn(
            $tableToken,
            'created_at',
            [
                'comment' => 'Token state',
                'type' => Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
            ]
        );

        $connection->addColumn(
            $tableToken,
            'updated_at',
            [
                'comment' => 'Token state',
                'type' => Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
            ]
        );

        $connection->addIndex(
            $tableToken,
            $connection->getIndexName(
                $tableToken,
                ['order_entity_id','token'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['order_entity_id','token'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );

        /** @var \Magento\Framework\DB\Select $select */
        $select = $connection
            ->select()->from(
                false,
                [
                    'order_entity_id' => 'soi.entity_id',
                    'created_at' => 'soi.created_at',
                    'updated_at' => 'soi.created_at',
                ])
            ->join(
                ["soi" => $this->moduleDataSetup->getTable('sales_order_grid')],
                new \Zend_Db_Expr("soi.increment_id = poiit.order_increment_id"),
                []
            );



        //Attention
        //
        $connection->query(
            $connection->updateFromSelect(
                $select,
                ["poiit" =>$tableToken]
            )
        );

    }
}
