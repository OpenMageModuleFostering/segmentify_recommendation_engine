<?php
class Segmentify_Engine_Block_Adminhtml_Campaign_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl(
                'sengine/index/edit',
                array(
                    '_current' => true,
                    'continue' => 0,
                )
            ),
            'method' => 'post',
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => $this->__('Campaign Details')
            )
        );

        $this->_addFieldsToFieldset($fieldset, array(
            'campaign' => array(
                'label' => $this->__('Campaign'),
                'input' => 'text',
                'disabled'=>true
            ),
            'selector' => array(
                'label' => $this->__('Selector'),
                'input' => 'text',
                'disabled' => true
            ),
            'items' => array(
                'label' => $this->__('Items'),
                'input' => 'text',
                'required' => true,
            ),
            'device' => array(
                'label' => $this->__('Device'),
                'input' => 'select',
                'required' => true,
                'values'    => array(
                    array('label'=>$this->__('All'),'value'=>'ALL'),
                    array('label'=>$this->__('Desktop'),'value'=>'PC'),
                    array('label'=>$this->__('Desktop & Tablet'),'value'=>'PCTABLET'),
                    array('label'=>$this->__('Phone & Tablet'),'value'=>'MOBILETABLET'),
                    array('label'=>$this->__('Phone'),'value'=>'MOBILE'),
                    array('label'=>$this->__('Tablet'),'value'=>'TABLET'),
                    array('label'=>$this->__('Application'),'value'=>'APPLICATION')
                )
            ),
            'test' => array(
                'label' => $this->__('Test'),
                'input' => 'select',
                'required' => true,
                'class'     => 'required-entry',
                'values'    => array(
                    array('label'=>'Test','value'=>1),
                    array('label'=>'Live','value'=>0)
                ),
            )
        ));

        return $this;
    }

    protected function _addFieldsToFieldset(Varien_Data_Form_Element_Fieldset $fieldset, $fields)
    {
        $requestData = new Varien_Object($this->getRequest()->getPost('campaignData'));

        foreach ($fields as $name => $_data) {
            if ($requestValue = $requestData->getData($name)) {
                $_data['value'] = $requestValue;
            }

            $_data['name'] = "campaignData[$name]";
            $_data['title'] = $_data['label'];

            if (!array_key_exists('value', $_data)) {
                $_data['value'] = $this->_getCampaign()->getData($name);
            }

            $fieldset->addField($name, $_data['input'], $_data);
        }

        return $this;
    }

    protected function _getCampaign()
    {
        if (!$this->hasData('campaign')) {
            $campaign = Mage::registry('current_campaign');

            if (!$campaign instanceof
                Segmentify_Engine_Model_Campaign) {
                $campaign = Mage::getModel(
                    'segmentify_engine/campaign'
                );
            }

            $this->setData('campaign', $campaign);
        }

        return $this->getData('campaign');
    }
}