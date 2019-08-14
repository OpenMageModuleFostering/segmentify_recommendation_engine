<?php
class Segmentify_Engine_Block_Adminhtml_Campaign_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'segmentify_engine';
        $this->_controller = 'adminhtml_campaign';
        $this->_mode = 'edit';
        $this->_headerText =  $this->__('Edit Campaign');
    }

    protected function _prepareLayout()
    {
        $campaign = Mage::registry('current_campaign');

        $this->_removeButton('delete');
        $this->_removeButton('reset');

        if($campaign->getActive()==1){
            $this->_addButton('deactivate', array(
                'label'   => $this->__('Deactivate'),
                'onclick' => "setLocation('{$this->getUrl('*/*/deactivate/id/'.$campaign->getId().'/')}')"
            ));
        } else {
            $this->_addButton('activate', array(
                'label'   => $this->__('Activate'),
                'onclick' => "setLocation('{$this->getUrl('*/*/activate/id/'.$campaign->getId().'/')}')"
            ));
        }


        return parent::_prepareLayout();
    }
}