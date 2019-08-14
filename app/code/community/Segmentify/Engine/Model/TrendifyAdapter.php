<?php

class Segmentify_Engine_Model_TrendifyAdapter extends Mage_Core_Model_Abstract
{
    const URL = 'https://panel.api.segmentify.com';
    const URL_T = 'http://panel-api.segmentify.com';

    public function getData($segmentifyId,$startDate,$endDate){
        $token = Mage::getSingleton('core/session')->getSegmentifyToken();
        if(!$token) return $this->formatData(null);

        $url = self::URL_T."/secure/v1/get/report/campaign_trend.json?startdate=".$startDate."&enddate=".$endDate."&limit=1000&interval=total&instanceid=".$segmentifyId;

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$token['access_token'],
            'Origin:'.$_SERVER['HTTP_HOST']
        ));
        $result = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($httpStatusCode==419){
            $adapter = Mage::getModel('segmentify_engine/adapter');
            $adapter->refreshToken();
            $result = $this->getData($segmentifyId,$startDate,$endDate);
        }

        if(is_null($result)){
            return false;
        }

        return $this->formatData($result);
    }


    private function formatData($data){
        $formattedData = array();
        $formattedData['view'] = 0;
        $formattedData['purchase'] = 0;
        $formattedData['basketAmount'] = 0;
        $formattedData['click'] = 0;
        $formattedData['impression'] = 0;
        $formattedData['purchaseAmount'] = 0;
        $formattedData['purchaseItems'] = 0;
        $formattedData['basketItems'] = 0;

        if(is_null($data)) return $formattedData;

        $data = json_decode($data,true);
        foreach($data as $v){
            if($v['x']=='Widget View') $formattedData['view'] = $v['y'];
            elseif($v['x']=='Purchases') $formattedData['purchase'] = $v['y'];
            elseif($v['x']=='Basket Amount') $formattedData['basketAmount'] = $v['y'];
            elseif($v['x']=='Impression') $formattedData['impression'] = $v['y'];
            elseif($v['x']=='Click') $formattedData['click'] = $v['y'];
            elseif($v['x']=='Purchase Amount') $formattedData['purchaseAmount'] = $v['y'];
            elseif($v['x']=='Purchased Items') $formattedData['purchaseItems'] = $v['y'];
            elseif($v['x']=='Basket Items') $formattedData['basketItems'] = $v['y'];
        }

        return $formattedData;
    }

}