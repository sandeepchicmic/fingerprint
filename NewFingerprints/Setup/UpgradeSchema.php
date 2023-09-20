<?php 
namespace Smartwave\NewFingerprints\Setup;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface{
 
	public function upgrade(SchemaSetupInterface $setup,ModuleContextInterface $context){
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('customer_image_archives')) {
            $table = $installer->getConnection()->newTable(
                            $installer->getTable('customer_image_archives')
                        )
                        ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                            null,
                            [
                                'nullable' => false,
                                'primary'  => true
                            ],
                            'Archive ID'
                        )
                        ->addColumn(
                            'cust_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                            null,
                            ['nullable => false'],
                            'Customer ID'
                        )
                        ->addColumn(
                            'image_name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            [],
                            'Fingerprint Image'
                        )
                        ->addColumn(
                            'image_date',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '255',
                            [],
                            'Fingerprint Date'
                        )
                        ->addColumn(
                            'created_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                            null,
                            ['nullable' => false],
                            'Created At'
                        )
                        ->setComment('Customer Images Archive Table');

                $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('customer_detail_archives')) {
            $table = $installer->getConnection()->newTable(
                            $installer->getTable('customer_detail_archives')
                        )
                        ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                            null,
                            [
                                'nullable' => false,
                                'primary'  => true
                            ],
                            'Archive ID'
                        )
                        ->addColumn(
                            'customer_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                            null,
                            ['nullable' => false],
                            'Customer Entity ID'
                        )
                        ->addColumn(
                            'name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            255,
                            ['nullable' => false],
                            'Deceased Name'
                        )
                        ->addColumn(
                            'agent_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '255',
                            ['nullable' => false],
                            'Deceased Id'
                        )
                        ->addColumn(
                            'date',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '255',
                            ['nullable' => false],
                            'Deceased Date'
                        )
                        ->addColumn(
                            'image',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '255',
                            ['nullable' => false],
                            'Deceased Fingerprint Image'
                        )
                        ->addColumn(
                            'first_name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '255',
                            ['nullable' => false],
                            'Customer Name'
                        )
                        ->addColumn(
                            'email',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '255',
                            ['nullable' => false],
                            'Customer Email'
                        )
                        ->addColumn(
                            'date_added',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                            null,
                            ['nullable' => false],
                            'Created At'
                        )
                        ->addColumn(
                            'status',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            11222222,
                            ['nullable' => false],
                            'Status'
                        )
                        ->addColumn(
                            'unique_upd_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            '100',
                            ['nullable' => true],
                            'UPD ID'
                        )
                        ->addColumn(
                            'verified_date',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                            null,
                            ['nullable' => true],
                            'Verified At'
                        )
                        ->addColumn(
                            'verified_user',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['nullable' => false, 'default' => 0],
                            '1 if verified else 0'
                        )
                        ->addColumn(
                            'deceased_fingerprint',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            11222222,
                            ['nullable' => true],
                            'Deceased Fingerprint'
                        )
                        ->setComment('Customer Details Archive Table');

                $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
    
}