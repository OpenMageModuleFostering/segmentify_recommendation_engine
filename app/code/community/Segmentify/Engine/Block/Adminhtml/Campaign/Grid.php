<?php
class Segmentify_Engine_Block_Adminhtml_Campaign_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('segmentify_engine/campaign_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'sengine/index/edit',
            array(
                'id' => $row->getId()
            )
        );
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header' => $this->__('ID'),
            'type' => 'number',
            'index' => 'id',
        ));

        $this->addColumn('campaign', array(
            'header' => $this->__('Campaign'),
            'type' => 'text',
            'index' => 'campaign',
        ));

        $this->addColumn('device', array(
            'header' => $this->__('Device'),
            'type' => 'options',
            'index' => 'device',
            'options'=>array(
                'ALL'=>$this->__('All'),
                'PC'=>$this->__('Desktop'),
                'PCTABLET'=>$this->__('Desktop &amp; Tablet'),
                'MOBILETABLET'=>$this->__('Phone &amp; Tablet'),
                'MOBILE'=>$this->__('Phone'),
                'TABLET'=>$this->__('Tablet'),
                'APPLICATION'=>$this->__('Application')
            )
        ));

        $this->addColumn('selector', array(
            'header' => $this->__('Selector'),
            'type' => 'text',
            'index' => 'selector',
        ));

        $this->addColumn('items', array(
            'header' => $this->__('Items'),
            'type' => 'number',
            'index' => 'items',
        ));

        $this->addColumn('test', array(
            'header' => $this->__('Test'),
            'type' => 'options',
            'index' => 'test',
            'options'=>array(0=>'Live',1=>'Test')
        ));

        $this->addColumn('active', array(
            'header' => $this->__('Active'),
            'type' => 'options',
            'index' => 'active',
            'options'=>array(0=>$this->__('Passive'),1=>$this->__('Active'))
        ));

        $this->addColumn('updated_at', array(
            'header' => $this->__('Updated'),
            'type' => 'datetime',
            'index' => 'updated_at',
        ));

        $this->addColumn('action', array(
            'header' => $this->__('Action'),
            'index' => 'id',
            'width'     => '100',
            'type'      => 'text',
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true,
            'renderer'  => 'Segmentify_Engine_Block_Adminhtml_Renderer_Actionlink',
        ));

        return parent::_prepareColumns();
    }

    protected function _getHelper()
    {
        return Mage::helper('segmentify_engine');
    }
}