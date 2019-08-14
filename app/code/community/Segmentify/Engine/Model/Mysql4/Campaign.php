<?php
/**
 * Created by PhpStorm.
 * User: ahmetzencirli
 * Date: 5.6.2016
 * Time: 19:13
 */ 
class Segmentify_Engine_Model_Mysql4_Campaign extends Mage_Core_Model_Mysql4_Abstract
{

    protected function _construct()
    {
        $this->_init('segmentify_engine/campaign', 'id');
    }

}