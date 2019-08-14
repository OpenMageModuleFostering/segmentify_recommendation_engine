<?php
class Segmentify_Engine_Model_Views {
    public function toOptionArray() {
        $collection = Mage::getModel('segmentify_engine/campaign')->getCollection();

        $options = array();
        foreach($collection as $campaign){
            $options[] = array(
                'value'=>$campaign->getData('selector'),
                'label'=>$campaign->getData('campaign')
            );
        }

        return $options;
    }
}