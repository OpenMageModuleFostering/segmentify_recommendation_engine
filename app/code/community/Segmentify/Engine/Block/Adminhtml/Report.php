<?php
class Segmentify_Engine_Block_Adminhtml_Report extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface {

    protected function _toHtml() {
        $html = $this->getHeader().$this->getBody();

        return $html;
    }

    private function getHeader(){
        $header = '
        <div class="content-header">
            <h3 class="icon-head head-adminhtml-campaign">'.$this->__('Campaign Report').'</h3>
            <p class="form-buttons">
            <button
                id="id_88d69c280acf9a4dfa00f2bb51dba338"
                title="Back"
                type="button"
                class="scalable back"
                onclick="setLocation(\''.Mage::helper("adminhtml")->getUrl("sengine/index/index").'\')"
                style="">
                <span><span><span>'.$this->__('Back').'</span></span></span>
            </button>
            </p>
        </div>
        ';

        return $header;
    }

    private function getBody(){
        $report = Mage::registry('report_data');

        $conversionWidget = ($report['view']/$report['impression'])*100;
        $conversionWidget = (is_infinite($conversionWidget) || is_nan($conversionWidget))?0.00:$conversionWidget;

        $conversionInteraction = ($report['click']/$report['view'])*100;
        $conversionInteraction = (is_infinite($conversionInteraction) || is_nan($conversionInteraction))?0.00:$conversionInteraction;

        $averageContributionBasket = $report['purchaseAmount']/$report['purchase'];
        $averageContributionBasket = (is_infinite($averageContributionBasket) || is_nan($averageContributionBasket))?0.00:$averageContributionBasket;

        $conversionBasket = ($report['basketItems']/$report['click'])*100;
        $conversionBasket = (is_infinite($conversionBasket) || is_nan($conversionBasket))?0.00:$conversionBasket;

        $conversionRevenue = ($report['purchaseItems']/$report['click'])*100;
        $conversionRevenue = (is_infinite($conversionRevenue) || is_nan($conversionRevenue))?0.00:$conversionRevenue;



        $body = '
        <style>
        .price{width:150px;height:150px;border-radius:100%;box-shadow:0 0 0 5px rgba(199,169,169,0.25);display:inline-block;white-space:normal!important;margin-top:20px;font-size:17px!important}
        .price+span{clear:both;display:block;margin:25px 0;line-height:25px}
        .entry-edit .entry-edit-head{text-align:center}
        .entry-edit .entry-edit-head h4{display:inline-block;float:none;font-size:17px;padding:5px 0}
        button.save{width:100%}
        .price span{margin-top:60px;width:99%;display:inline-block;line-height:28px;font-size:18px!important}
        
        .grid table td.last a {
            background: #5d5d5d;
            color: #fff;
            border-radius: 3px;
            padding: 3px;
            width: 35px;
            float: left;
            margin-left: 1px;
            font-size: 11px;
            text-align: center;
            text-decoration: none;
            margin-top: 4px;
            margin-bottom: 4px;
        }
        
        .grid table td.last a:last-child{
            margin-left: 6px;
        }
        
        .grid table td a:hover{
            background: #e21717;
        }
        
        .reports td:nth-child(2),
        .reports td:nth-child(3),
        .reports td:nth-child(3),
        .reports td:nth-child(4),
        .reports td:nth-child(5) {
            width: 21%;
        }
        
        
        .reports fieldset.a-center.bold {
            height: 306px;
        }
                
        </style>
        <table cellspacing="25" width="100%" class="reports">
            <tbody>
                <tr>
                    <td width="200">'.$this->getFilters().'</td>
                    <td>
                        <div class="entry-edit">
                            <div class="entry-edit-head"><h4>'.$this->__('Widget').'</h4></div>
                            <fieldset class="a-center bold">
                                <span class="nowrap" style="font-size:18px;">
                                    <span class="price">
                                        <span style="font-size:24px">'.$this->format($report['impression']).' '.$this->__('views').'</span>
                                    </span>
                                    <span style="color:#999">
                                        '.$this->__('Actual View').': '.$this->format($report['view']).'<br>
                                        '.$this->__('Conversion').': '.$this->format($conversionWidget,true).'%
                                    </span>
                                </span>
                            </fieldset>
                        </div>

                    </td>
                    <td>
                        <div class="entry-edit">
                            <div class="entry-edit-head"><h4>'.$this->__('Interaction').'</h4></div>
                            <fieldset class="a-center bold">
                                <span class="nowrap" style="font-size:18px;">
                                    <span class="price">
                                        <span style="font-size:24px">'.$this->format($report['click']).' '.$this->__('clicks').'</span>
                                        
                                    </span>
                                    <span style="color:#999">
                                            '.$this->__('Conversion').': '.$this->format($conversionInteraction,true).'%
                                        </span>
                                </span>
                            </fieldset>
                        </div>
                    </td>
                    <td>
                        <div class="entry-edit">
                            <div class="entry-edit-head"><h4>'.$this->__('Basket').'</h4></div>
                            <fieldset class="a-center bold">
                                <span class="nowrap" style="font-size:18px;">
                                    <span class="price">
                                        <span style="font-size:24px">'.$this->format($report['basketItems']).' '.$this->__('items').'</span>
                                        
                                    </span>
                                    <span style="color:#999">
                                            '.$this->__('Total Amount').': '.$this->format($report['basketAmount']).' ₺<br>
                                            '.$this->__('Average Contribution').': '.$this->format( $averageContributionBasket,true).' ₺<br>
                                            '.$this->__('Conversion').': '.$this->format($conversionBasket,true).'%
                                        </span>
                                </span>
                            </fieldset>
                        </div>

                    </td>
                    <td>
                        <div class="entry-edit">
                            <div class="entry-edit-head"><h4>'.$this->__('Revenue').'</h4></div>
                            <fieldset class="a-center bold">
                                <span class="nowrap" style="font-size:18px;">
                                    <span class="price">
                                        <span style="font-size:24px">'.$this->format($report['purchaseAmount']).' '.$this->__('Turkish Lira').'</span>
                                    </span>
                                    <span style="color:#999">
                                            '.$this->__('Purchases').': '.$this->format($report['purchase']).'<br>
                                            '.$this->__('Purchased Items').': '.$this->format($report['purchaseItems']).'<br>
                                            '.$this->__('Conversion').': '.$this->format($conversionRevenue,true).'%
                                        </span>
                                </span>
                            </fieldset>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        ';

        return $body;
    }

    private function getFilters(){
        $campaign = Mage::registry('current_campaign');
        $formData = Mage::registry('form_data');
        $showCustom = false;
        $interval = 'week';
        $dateFrom = '';
        $dateTo = '';
        if(!is_null($formData)){
            if($formData['date']=='custom')$showCustom = true;

            $interval = $formData['date'];
            $dateFrom = $formData['date_from'];
            $dateTo = $formData['date_to'];
        }

        $filters = '
        <div class="entry-edit">
            <div class="entry-edit-head"><h4>Filter</h4></div>
            <form id="filter_form" action="'.Mage::helper("adminhtml")->getUrl('sengine/index/report', array('id'=>$campaign->getId())).'" method="post">
            '.$this->getBlockHtml('formkey').'

            <fieldset>
                <div style="margin-bottom:5px">
                    <select id="date" name="date" title="'.$this->__('Date').'" style="width:100%;padding:3px" onchange="custom()">
                        <option '.(($interval=='today')?'selected':'').' value="today">'.$this->__('Today').'</option>
                        <option '.(($interval=='week')?'selected':'').' value="week">'.$this->__('Week').'</option>
                        <option '.(($interval=='month')?'selected':'').' value="month">'.$this->__('Month').'</option>
                        <option '.(($interval=='all')?'selected':'').' value="all">'.$this->__('All').'</option>
                        <option '.(($interval=='custom')?'selected':'').' value="custom">'.$this->__('Custom').'</option>
                    </select>
                </div>

                <div id="customdate" '.((!$showCustom)?'style="display:none"':'').'>
                    <div style="margin-bottom:1px">
                        Start:<br>
                        <input readonly type="text" id="date_from" name="date_from" style="width:80%;padding:3px" value="'.$dateFrom.'">
                        <img title="'.$this->__('Select date').'" id="date_from_trig"
                        src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'adminhtml/default/default/images/grid-cal.gif"  class="v-middle"/>
                    </div>
                    <div>
                        End:<br>
                        <input readonly type="text" id="date_to" name="date_to" style="width:80%;padding:3px" value="'.$dateTo.'">
                        <img title="'.$this->__('Select date').'" id="date_to_trig"
                        src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'adminhtml/default/default/images/grid-cal.gif"  class="v-middle"/>
                    </div>
                </div>
                <br>


                <div>
                    <button id="id_2add93d7c621d979ebe3ca3bf1a473e3" title="'.$this->__('Save').'" type="submit" class="scalable save" style="">
                    <span><span><span>'.$this->__('Apply').'</span></span></span>
                    </button>
                </div>

             </fieldset>
            </form>
        </div>
        ';

        $script = "
        <script type='text/javascript'>
        function custom(){
            var e = document.getElementById('date');
            var v = e.options[e.selectedIndex].value;

            if(v=='custom'){
                document.getElementById('customdate').style.display = 'block';
            } else {
                document.getElementById('customdate').style.display = 'none';
            }
        }


        Calendar.setup({
            inputField : 'date_from',
            ifFormat : '%d.%m.%Y',
            button : 'date_from_trig',
            align : 'Bl',
            singleClick : true
        });

        Calendar.setup({
            inputField : 'date_to',
            ifFormat : '%d.%m.%Y',
            button : 'date_to_trig',
            align : 'Bl',
            singleClick : true
        });
        </script>
        ";
        return $filters.$script;
    }

    private function format($string,$conversion = false){
        if(!$conversion){
            return $string;
        } else {
            return number_format($string,2);
        }

    }
}