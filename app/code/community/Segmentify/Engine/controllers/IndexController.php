<?php

class Segmentify_Engine_IndexController extends Mage_Adminhtml_Controller_Action {

    private function initialize(){

        if(!Mage::getSingleton('core/session')->getSegmentifyToken()){
            if(!Mage::getSingleton('core/session')->getSegmentifyCode()){
                $code = $this->getRequest()->getParam('code');

                if(is_null($code) || $code==''){
                    $clientId = '21b852c6b73d';
                    $currentUrl = Mage::helper('core/url')->getCurrentUrl();

                    $this->_redirectUrl('https://www.segmentify.com/auth/login.html?client_id='.$clientId.'&redirect_uri='.$currentUrl.'&response_type=code');
                } else {
                    Mage::getSingleton('core/session')->setSegmentifyCode($code);
                    $currentUrl = Mage::helper('core/url')->getCurrentUrl();
                    $this->_redirectUrl($currentUrl);
                }
            } else {

                $adapter = Mage::getModel('segmentify_engine/adapter');
                if(!$adapter->initToken()){
                    $this->_getSession()->addError(
                        $this->__('Code: 1001, A technical problem has occurred with the application - please try again later!')
                    );
                }
            }
        } else {
            $adapter = Mage::getModel('segmentify_engine/adapter');
            if(!$adapter->checkExtCreated()){
                $this->_getSession()->addError(
                    $this->__('Code: 1002, A technical problem has occurred with the application - please try again later!')
                );
            } else {
                $this->controlLicence();
            }
        }


    }

    private function controlLicence(){
        $licence = Mage::getModel('segmentify_engine/licence');

        if(!$licence->check()){
            $this->_getSession()->addWarning(
                $this->__('Your Segmentify licence has exprired! <a target="_blank" href="https://panel.segmentify.com/me/payments">Go to Payment Page</a>')
            );
        }
    }

    public function indexAction()
    {
        $this->initialize();

        $block = $this->getLayout()->createBlock('segmentify_engine/adminhtml_campaign');

        $this->loadLayout()
            ->_addContent($block)
            ->_setActiveMenu('segmentify_menu')
            ->renderLayout();
    }

    public function reportAction()
    {
        $this->initialize();

        $campaign = Mage::getModel('segmentify_engine/campaign');
        if ($campaignId = $this->getRequest()->getParam('id', false)) {
            $campaign->load($campaignId);

            if (!$campaign->getId()){
                $this->_getSession()->addError(
                    $this->__('This campaign no longer exists.')
                );

                return $this->_redirect(
                    'sengine/index'
                );
            }
        }

        $startTime = strtotime('-1 week').'000';
        $endTime = strtotime('now').'000';

        if($date = $this->getRequest()->getPost('date')){
            Mage::register('form_data',$this->getRequest()->getPost());

            switch($date){
                case 'today':
                    $startTime = strtotime('-1 day').'000';
                    $endTime = strtotime('now').'000';
                    break;
                case 'week':
                    $startTime = strtotime('-1 week').'000';
                    $endTime = strtotime('now').'000';
                    break;
                case 'month':
                    $startTime = strtotime('-1 month').'000';
                    $endTime = strtotime('now').'000';
                    break;
                case 'all':
                    $startTime = strtotime('01/01/2010').'000';
                    $endTime = strtotime('now').'000';
                    break;
                case 'custom':
                    $start = $this->getRequest()->getPost('date_from');
                    $end = $this->getRequest()->getPost('date_to');
                    //herhangi biri bosmu
                    if(trim($start)=='' || trim($end)==''){
                        $this->_getSession()->addError(
                            $this->__('Custom date fields can not be empty!')
                        );
                    } else {
                        $now = strtotime('now').'000';
                        $startTime = strtotime(date('Y-m-d 00:00:00', strtotime($start))).'000';
                        $endTime = strtotime(date('Y-m-d 00:00:00', strtotime($end))).'000';
                        
                        if($startTime>$now || $endTime>$now){
                            $this->_getSession()->addError(
                                $this->__('Start or end date can not be after than now!')
                            );
                        } else {
                            if($startTime>$endTime){
                                $this->_getSession()->addError(
                                    $this->__('Start date can not be after than end date!')
                                );
                            } elseif($endTime<$startTime){
                                $this->_getSession()->addError(
                                    $this->__('End date can not be before than start date!')
                                );
                            }
                        }


                    }

                    break;
                default:
                    $this->_getSession()->addError(
                        $this->__('Interval is not correct!')
                    );
                    break;
            }
        }

        //connect to trendify
        $trendifyAdapter = Mage::getModel('segmentify_engine/trendifyAdapter');
        $data = $trendifyAdapter->getData($campaign->getData('segmentify_id'),$startTime,$endTime);
        Mage::register('report_data', $data);

        Mage::register('current_campaign', $campaign);

        $block = $this->getLayout()->createBlock('segmentify_engine/adminhtml_report');

        $this->loadLayout()
            ->_addContent($block)
            ->_setActiveMenu('segmentify_menu')
            ->renderLayout();
    }

    public function editAction()
    {
        $this->initialize();

        $campaign = Mage::getModel('segmentify_engine/campaign');

        if ($campaignId = $this->getRequest()->getParam('id', false)) {
            $campaign->load($campaignId);

            if (!$campaign->getId()){
                $this->_getSession()->addError(
                    $this->__('This campaign no longer exists.')
                );

                return $this->_redirect(
                    'sengine/index'
                );
            }
        }

        if ($postData = $this->getRequest()->getPost('campaignData')) {
            try {
                $success = false;
                $adapter = Mage::getModel('segmentify_engine/adapter');
                $resultJson = $adapter->updateCampaign(
                    $adapter->getAccountId(),
                    $campaign->getData('segmentify_id'),
                    $postData['device'],
                    $campaign->getData('campaign'),
                    $postData['items'],
                    $campaign->getData('selector'),
                    ($postData['test']==1)?true:false
                );

                $result = json_decode($resultJson,true);

                if(isset($result['status'])){
                    if($result['status']=='OK'){
                        $success = true;
                    }
                }

                if($success){
                    $postData['updated_at'] = date('Y-m-d H:i:s');
                    $campaign->addData($postData);
                    $campaign->save();

                    $this->_getSession()->addSuccess(
                        $this->__('Campaign has been updated.')
                    );
                } else {
                    $this->_getSession()->addError(
                        $result['message']
                    );
                }

                return $this->_redirect(
                    'sengine/index/edit',
                    array('id' => $campaign->getId())
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }

        Mage::register('current_campaign', $campaign);


        $editBlock = $this->getLayout()->createBlock(
            'segmentify_engine/adminhtml_campaign_edit'
        );

        $this->loadLayout()
            ->_addContent($editBlock)
            ->_setActiveMenu('segmentify_menu')
            ->renderLayout();
    }

    public function deactivateAction(){
        $id = $this->getRequest()->getParam('id');
        $campaign = Mage::getModel('segmentify_engine/campaign')->load($id);
        $adapter = Mage::getModel('segmentify_engine/adapter');
        $success = false;

        $resultJson = $adapter->deactivate($adapter->getAccountId(),$campaign->getData('segmentify_id'));
        $result = json_decode($resultJson,true);

        if(isset($result['status'])){
            if($result['status']=='OK'){
                $success = true;
            }
        }

        if($success){
            $campaign->setData('active',0);
            $campaign->save();

            $this->_getSession()->addSuccess(
                $this->__('Campaign deactivated.')
            );
        } else {
            $this->_getSession()->addError($result['message']);
        }

        return $this->_redirect(
            'sengine/index/edit',
            array('id' => $id)
        );

    }

    public function activateAction(){
        $id = $this->getRequest()->getParam('id');
        $campaign = Mage::getModel('segmentify_engine/campaign')->load($id);
        $adapter = Mage::getModel('segmentify_engine/adapter');
        $success = false;

        $resultJson = $adapter->activate($adapter->getAccountId(),$campaign->getData('segmentify_id'));
        $result = json_decode($resultJson,true);

        if(isset($result['status'])){
            if($result['status']=='OK'){
                $success = true;
            }
        }

        if($success){
            $campaign->setData('active',1);
            $campaign->save();

            $this->_getSession()->addSuccess(
                $this->__('Campaign activated.')
            );
        } else {
            $this->_getSession()->addError($result['message']);
        }


        return $this->_redirect(
            'sengine/index/edit',
            array('id' => $id)
        );

    }

} 