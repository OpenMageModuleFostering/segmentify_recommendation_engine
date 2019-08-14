<?php

class Segmentify_Engine_Model_Adapter extends Mage_Core_Model_Abstract
{
    const URL = 'http://rest.segmentify.com';
    const URL_OAUTH = 'https://panel-api.segmentify.com';
    const CLIENT_ID = '21b852c6b73d';
    const CLIENT_SECRET = '7c468666cf0bc888e81737904ff';

    public function initToken(){
        $post = "client_secret=".self::CLIENT_SECRET.
            "&client_id=".self::CLIENT_ID.
            "&code=".Mage::getSingleton('core/session')->getSegmentifyCode().
            "&redirect_uri=".$_SERVER['HTTP_HOST'].
            "&grant_type=authorization_code";

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, self::URL_OAUTH.'/token');
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Origin:'.$_SERVER['HTTP_HOST']

        ));
        $result = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($result,true);

        Mage::getSingleton('core/session')->unsSegmentifyCode();

        if(isset($result['access_token'])){
            Mage::getSingleton('core/session')->setSegmentifyToken($result);
            return true;
        } else {
            return false;
        }

    }

    public function refreshToken(){
        $token = Mage::getSingleton('core/session')->getSegmentifyToken();

        $post = "client_secret=".self::CLIENT_SECRET.
            "&client_id=".self::CLIENT_ID.
            "&code=".Mage::getSingleton('core/session')->getSegmentifyCode().
            "&redirect_uri=".$_SERVER['HTTP_HOST'].
            "&refresh_token=".$token['refresh_token'].
            "&grant_type=refresh_token";

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, self::URL_OAUTH.'/token');
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Origin:'.$_SERVER['HTTP_HOST']

        ));
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);

        if(isset($result['access_token'])){
            Mage::getSingleton('core/session')->unsSegmentifyToken();
            Mage::getSingleton('core/session')->setSegmentifyToken($result);
            return true;
        } else {
            return false;
        }

    }

    public function checkExtCreated(){
        if($accountId = $this->getAccountId()){
            $extHome = $this->campaignDetails($accountId,'ext_home_rec');
            $extHome = json_decode($extHome,true);

            if(isset($extHome['status'])){
                if($extHome['status']=='FAIL'){
                     return $this->registerCampaigns();
                } else {
                    return true;
                }
            }

        } else {
            return false;
        }
    }

    public function getAccountId(){
        //get account id
        $token = Mage::getSingleton('core/session')->getSegmentifyToken();

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, self::URL_OAUTH.'/secure/v1/get/user/current.json');
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Origin:'.$_SERVER['HTTP_HOST'],
            'Authorization: Bearer '.$token['access_token']

        ));
        $result = curl_exec($curl);

        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($httpStatusCode==419){
            $this->refreshToken();
            $result = $this->getAccountId();
        }

        $result = json_decode($result,true);
        if(isset($result['account']['accountId'])){
            if(isset($result['account']['apiKey'])){
                try{
                    $connect = Mage::getModel('segmentify_engine/connect')->load(1);

                    if($connect->getData('apikey')=='' && $connect->getData('account_id')==''){
                        $connect->addData(
                            [
                                'apikey'=>$result['account']['apiKey'],
                                'account_id'=>$result['account']['accountId'],
                                'licence_ts'=> strtotime('now'),
                                'active'=>true,
                                'updated_at'=>date('Y-m-d H:i:s')
                            ]
                        );
                        $connect->save();
                    } else {
                        if(isset($result['status'])){
                            $connect->addData(
                                [
                                    'licence_ts'=> strtotime('now'),
                                    'active'=>($result['status']=='ACTIVE')?true:false,
                                    'updated_at'=>date('Y-m-d H:i:s')
                                ]
                            );
                            $connect->save();
                        }

                    }

                } catch(Exception $e){
                    //cannot set apikey
                }

            }

            return $result['account']['accountId'];
        } else {
            return false;
        }
    }

    public function registerCampaigns(){
        $token = Mage::getSingleton('core/session')->getSegmentifyToken();
        $queryString = "/external/campaign/register";

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, self::URL.$queryString);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl,CURLOPT_POSTFIELDS, '');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Origin:'.$_SERVER['HTTP_HOST'],
            'Authorization: Bearer '.$token['access_token']
        ));
        $result = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($result,true);

        if(isset($result['status'])){
            if($result['status']=='OK'){
                return true;
            } else {
                return false;
            }
        }
    }

    public function updateCampaign($accountId,$campaignId,$device,$title,$items,$selector,$test){
        $token = Mage::getSingleton('core/session')->getSegmentifyToken();
        $queryString = "/external/campaign/update/".$accountId."/".$campaignId;
        $post = [
            'testMode'=>$test,
            'device'=>$device,
            'title'=>$title,
            'items'=>$items,
            'selector'=>$selector
        ];

        $postJson = json_encode($post);

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, self::URL.$queryString);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_POSTFIELDS, $postJson);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Origin:'.$_SERVER['HTTP_HOST'],
            'Authorization: Bearer '.$token['access_token']

        ));
        $result = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($httpStatusCode==419){
            $this->refreshToken();
            $result = $this->updateCampaign($accountId,$campaignId);
        }

        return $result;
    }

    public function campaignDetails($accountId,$campaignId){
        $token = Mage::getSingleton('core/session')->getSegmentifyToken();
        $queryString = "/external/campaign/details/".$accountId."/".$campaignId;

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, self::URL.$queryString);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Origin:'.$_SERVER['HTTP_HOST'],
            'Authorization: Bearer '.$token['access_token']
        ));
        $result = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($httpStatusCode==419){
            $this->refreshToken();
            $result = $this->campaignDetails($accountId,$campaignId);
        }

        return $result;
    }

    public function activate($accountId,$campaignId){
        $token = Mage::getSingleton('core/session')->getSegmentifyToken();
        $queryString = "/external/campaign/activate/".$accountId."/".$campaignId;

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, self::URL.$queryString);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Origin:'.$_SERVER['HTTP_HOST'],
            'Authorization: Bearer '.$token['access_token']
        ));
        $result = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($httpStatusCode==419){
            $this->refreshToken();
            $result = $this->activate($accountId,$campaignId);
        }

        return $result;
    }


    public function deactivate($accountId,$campaignId){
        $token = Mage::getSingleton('core/session')->getSegmentifyToken();
        $queryString = "/external/campaign/deactivate/".$accountId."/".$campaignId;

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, self::URL.$queryString);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Origin:'.$_SERVER['HTTP_HOST'],
            'Authorization: Bearer '.$token['access_token']
        ));
        $result = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($httpStatusCode==419){
            $this->refreshToken();
            $result = $this->deactivate($accountId,$campaignId);
        }

        return $result;
    }

}