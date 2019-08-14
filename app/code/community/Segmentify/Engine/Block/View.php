<?php
class Segmentify_Engine_Block_View extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface {

    protected function _toHtml() {
        $html = '';
        $licence = Mage::getModel('segmentify_engine/licence');

        if($licence->checkDaily()){
            $option = $this->getData('view_option');
            $html = '<div id="'.$option.'"></div>';
        }

        return $html;
    }
}