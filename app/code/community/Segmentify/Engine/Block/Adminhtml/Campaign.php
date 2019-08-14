<?php
class Segmentify_Engine_Block_Adminhtml_Campaign extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'segmentify_engine';
        $this->_controller = 'adminhtml_campaign';
        $this->_headerText = $this->__('Campaigns');

        $this->_removeButton('add');

    }

}