<?php
$installer = $this;
$installer->startSetup();

$tableCampaign = $installer->getConnection()
    ->newTable($installer->getTable('segmentify_engine/campaign'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('campaign', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Campaign')
    ->addColumn('selector', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Selector')
    ->addColumn('segmentify_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Segmentify ID')
    ->addColumn('items', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'=>4
    ), 'Items')
    ->addColumn('test', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'=>false
    ), 'Test/Live')
    ->addColumn('active', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'=>true
    ), 'Active/Passive')
    ->addColumn('device', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false
    ), 'Device')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false
    ), 'Update Time');

$installer->getConnection()->createTable($tableCampaign);

$tableConnect = $installer->getConnection()
    ->newTable($installer->getTable('segmentify_engine/connect'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('apikey', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false
    ), 'API Key')
    ->addColumn('account_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false
    ), 'Account ID')
    ->addColumn('licence_ts', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false
    ), 'Licence TS')
    ->addColumn('active', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'=>true
    ), 'Active/Passive')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false
    ), 'Update Time');

$installer->getConnection()->createTable($tableConnect);


$installer->endSetup();
