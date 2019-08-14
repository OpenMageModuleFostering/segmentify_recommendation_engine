<?php

class Segmentify_Engine_Model_Licence extends Mage_Core_Model_Abstract
{

    public function check(){
        $adapter = Mage::getModel('segmentify_engine/adapter');
        $connect = Mage::getModel('segmentify_engine/connect')->load(1);

        $homeCampaign = Mage::getModel('segmentify_engine/campaign')->load('ext_home_rec','segmentify_id');
        $remoteCampaign = $adapter->campaignDetails($connect->getData('account_id'),$homeCampaign->getData('segmentify_id'));
        $remoteCampaignDec = json_decode($remoteCampaign,true);

        if($connect->getData('api_key')!=''){
            if(array_key_exists('status',$remoteCampaignDec)){
                if($remoteCampaignDec['status']=='FAIL'){
                    $connect->setData('licence_ts',strtotime('now'));
                    $connect->setData('active',0);
                    $connect->save();

                    return false;
                }
            }
        }

        $connect->setData('licence_ts',strtotime('now'));
        $connect->setData('active',1);
        $connect->save();

        return true;
    }
    
    public function checkDaily(){
        $adapter = Mage::getModel('segmentify_engine/adapter');
        $connect = Mage::getModel('segmentify_engine/connect')->load(1);

        if((strtotime('now')-$connect->getData('licence_ts'))<=(3600*24)){
            return $connect->getData('active');
        }

        $homeCampaign = Mage::getModel('segmentify_engine/campaign')->load('ext_home_rec','segmentify_id');
        $remoteCampaign = $adapter->campaignDetails($connect->getData('account_id'),$homeCampaign->getData('segmentify_id'));
        $remoteCampaignDec = json_decode($remoteCampaign,true);

        if($connect->getData('api_key')!=''){
            if(array_key_exists('status',$remoteCampaignDec)){
                if($remoteCampaignDec['status']=='FAIL'){
                    $connect->setData('licence_ts',strtotime('now'));
                    $connect->setData('active',0);
                    $connect->save();

                    return false;
                }
            }
        }

        $connect->setData('licence_ts',strtotime('now'));
        $connect->setData('active',1);
        $connect->save();

        return true;
    }

}