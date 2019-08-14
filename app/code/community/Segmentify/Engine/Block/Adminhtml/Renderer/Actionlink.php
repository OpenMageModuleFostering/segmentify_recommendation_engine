<?php

class Segmentify_Engine_Block_Adminhtml_Renderer_Actionlink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $urlEdit = $this->getUrl('sengine/index/edit', array('id'=>$row->getId(), 'storeId' => $row->getStoreId()));
        $urlReport = $this->getUrl('sengine/index/report', array('id'=>$row->getId(), 'storeId' => $row->getStoreId()));
        return
            sprintf("<a href='%s'>%s</a>", $urlEdit, $this->__('Edit')).' '.
            sprintf("<a href='%s'>%s</a>", $urlReport, $this->__('Report'))
            ;

    }
}