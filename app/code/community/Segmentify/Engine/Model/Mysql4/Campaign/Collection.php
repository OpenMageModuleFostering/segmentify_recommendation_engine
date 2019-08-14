<?php
/**
 * Created by PhpStorm.
 * User: ahmetzencirli
 * Date: 5.6.2016
 * Time: 19:15
 */ 
class Segmentify_Engine_Model_Mysql4_Campaign_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
         $this->_init('segmentify_engine/campaign');
    }

}