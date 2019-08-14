<?php

class Segmentify_Engine_Model_Observer {

    public function checkout(Varien_Event_Observer $observer){
        $order = $observer->getEvent()->getOrder();
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());

        $items = [];
        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $item = [
                "productId"=>$item->getProduct()->getSku(),
                "quantity"=>$item->getQty(),
                "price"=>(float)$item->getProduct()->getFinalPrice()
            ];
            $items[] = $item;
        }

        $checkoutPurchase = "
        <script>
            Segmentify('checkout:purchase', {
            basketId: '".$quote->getId()."',
            totalPrice: ".(float)$order->getGrandTotal().",
            productList: ".json_encode($items)."
        });
        </script>
        ";

        Mage::getSingleton('core/session')->setCheckoutPurchase($checkoutPurchase);

    }

    public function userUpdate($observer){
        $customer = $observer->getEvent()->getCustomer();
        $firstname = $customer->getFirstname();
        $lastname= $customer->getLastname();
        $email = $customer->getEmail();
        $date = date('d.m.Y',strtotime($customer->getCreated_at()));

        $userUpdated = "
            <script>
            Segmentify('user:update', {
                username: '".$email."',
                fullName: '".$firstname.' '.$lastname."',
                email: '".$email."',
                phone: '',
                gender: '',
                age: '',
                segments: [],
                memberSince: '".$date."'
            });
            </script>
        ";

        Mage::getSingleton('core/session')->setUserUpdated($userUpdated);
    }

    public function signout(Varien_Event_Observer $observer){
        $userSignout = "
            <script>
            Segmentify('user:signout');
            </script>
        ";

        Mage::getSingleton('core/session')->setUserSignout($userSignout);
    }


    public function signin(Varien_Event_Observer $observer){
        $customer = $observer->getCustomer();
        $date = date('d.m.Y',strtotime($customer->getCreated_at()));

        $userSignin = "
            <script>
            Segmentify('user:signin', {
                username: '".$customer->getEmail()."'
            });
            Segmentify('user:update', {
                username: '".$customer->getEmail()."',
                fullName: '".$customer->getFirstname().' '.$customer->getLastname()."',
                email: '".$customer->getEmail()."',
                phone: '',
                gender: '',
                age: '',
                segments: [],
                memberSince: '".$date."'
            });
            </script>
        ";

        Mage::getSingleton('core/session')->setUserSignin($userSignin);

    }

    public function registerUser(Varien_Event_Observer $observer){
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        $email = $customer->getEmail();
        $firstname = $customer->getFirstname();
        $lastname = $customer->getLastname();

        $userCreated = "
            <script>
            Segmentify('user:signup', {
                username: '".$email."',
                email: '".$email."',
                fullName: '".$firstname.' '.$lastname."',
                phone:'',
                gender:'',
                age:''
            });
            Segmentify('user:signin', {
                username: '".$email."'
            });
            </script>
        ";

        Mage::getSingleton('core/session')->setUserCreated($userCreated);

    }

    public function removeFromCart($observer){
        $product = $observer->getEvent()->getQuoteItem()->getProduct();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $salesQuoteItem = Mage::getModel('sales/quote_item')->getCollection()
            ->setQuote($quote)
            ->addFieldToFilter('quote_id', $quote->getId())
            ->addFieldToFilter('product_id', $product->getId())
            ->getFirstItem();


        $removeFromCart = "
            <script>
            Segmentify('basket:remove', {
                productId: '".$product->getSku()."',
                basketId: ".$quote->getId().",
                quantity:".(int)$salesQuoteItem->getQty()."
            });
            </script>
        ";

        Mage::getSingleton('core/session')->setCartR($removeFromCart);


    }

    public function addToCart(){
        $session        = Mage::getSingleton('checkout/session');
        $product = Mage::getModel('catalog/product')->load(Mage::app()->getRequest()->getParam('product', 0));
        if (!$product->getId()) {
            return;
        }

        $quoteId = ($session->getQuoteId()=='')?"''": $session->getQuoteId();

        $addToCart = "
        <script>
        Segmentify('basket:add', {
            productId: '".$product->getSku()."',
            basketId: ".$quoteId.",
            price:".(float)$product->getPrice().",
            quantity:".(int)Mage::app()->getRequest()->getParam('qty', 1)."
        });
        </script>
        ";

        Mage::getSingleton('core/session')->setCartA($addToCart);

    }

    public function view($observer){
        try{
            if(Mage::getSingleton('admin/session')->getUser()!=null){
                return;
            }

        } catch(Exception $e){
            //do nothing
        }

        try{
            $connectToSegmentify = Mage::getModel('segmentify_engine/connect')->load(1);
        } catch(Exception $e){
            return;
        }



        $apiKey = $connectToSegmentify->getApikey();
        if($apiKey==''){
            return;
        }

        $licence = Mage::getModel('segmentify_engine/licence');
        if(!$licence->checkDaily()){
            return;
        }



        $controller = Mage::app()->getRequest()->getControllerName();
        $action = Mage::app()->getRequest()->getActionName();
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();


        //checkout success
        if($controller=='onepage' && $action!='success') return;

        if($checkoutPurchase = Mage::getSingleton('core/session')->getCheckoutPurchase()){
            echo $checkoutPurchase;
            Mage::getSingleton('core/session')->unsCheckoutPurchase();
        }

        //addtocart
        if($controller=='cart' && $action=='add') return;

        if($addToCart = Mage::getSingleton('core/session')->getCartA()){
            echo $addToCart;
            Mage::getSingleton('core/session')->unsCartA();
        }

        //remove from cart
        if($removeFromCart = Mage::getSingleton('core/session')->getCartR()){
            echo $removeFromCart;
            Mage::getSingleton('core/session')->unsCartR();
        }

        switch($controller){
            case 'account':
                echo "
                <script>
                Segmentify('view:page', {
                    pageUrl: '".$currentUrl."',
                    referrer: '".$_SERVER['HTTP_REFERER']."',
                });
                </script>
                ";

                if($action=='index'){
                    if($userCreated = Mage::getSingleton('core/session')->getUserCreated()){
                        echo $userCreated;
                        Mage::getSingleton('core/session')->unsUserCreated();
                    } else {
                        if($userSignin = Mage::getSingleton('core/session')->getUserSignin()){
                            echo $userSignin;
                            Mage::getSingleton('core/session')->unsUserSignin();
                        } else {
                            if($userUpdated = Mage::getSingleton('core/session')->getUserUpdated()){
                                echo $userUpdated;
                                Mage::getSingleton('core/session')->unsUserUpdated();
                            }
                        }
                    }


                }

                if($action=='logoutSuccess'){
                    if($userSignout = Mage::getSingleton('core/session')->getUserSignout()){
                        echo $userSignout;
                        Mage::getSingleton('core/session')->unsUserSignout();
                    }
                }
                break;
            case 'cart':
                echo "
                <script>
                Segmentify('view:page', {
                    pageUrl: '".$currentUrl."',
                    category: 'Basket Page',
                    referrer: '".$_SERVER['HTTP_REFERER']."',
                });
                </script>
                ";

                if($action=='index'){
                    //checkout:basket
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $quoteData = $quote->getData();
                    $grandTotal = $quoteData['grand_total'];

                    $items = [];
                    foreach ($quote->getAllItems() as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }

                        $item = [
                            "productId"=>$item->getProduct()->getSku(),
                            "quantity"=>$item->getQty(),
                            "price"=>(float)$item->getProduct()->getFinalPrice()
                        ];
                        $items[] = $item;
                    }

                    echo "
                    <script>
                        Segmentify('checkout:basket', {
                        basketId: '".$quote->getId()."',
                        totalPrice: ".(float)$grandTotal.",
                        productList: ".json_encode($items)."
                    });
                    </script>
                    ";
                }
                break;
            case 'index':

                //404 page
                if($action=='noRoute'){
                    echo "
                    <script>
                    Segmentify('view:page', {
                        pageUrl: '".$currentUrl."',
                        category: '404 Page',
                        referrer: '".$_SERVER['HTTP_REFERER']."'
                    });
                    </script>
                    ";
                } else {
                    echo "
                    <script>
                    Segmentify('view:page', {
                        pageUrl: '".$currentUrl."',
                        category: 'Home Page',
                        referrer: '".$_SERVER['HTTP_REFERER']."'
                    });
                    </script>
                    ";
                }

                break;
            case 'category':
                $iCurrentCategory = Mage::registry('current_category')->getName();

                echo "
                <script>
                Segmentify('view:page', {
                    pageUrl: '".$currentUrl."',
                    category: 'Category Page',
                    subCategory:'".$iCurrentCategory."',
                    referrer: '".$_SERVER['HTTP_REFERER']."'
                });
                </script>
                ";
                break;
            case 'product':
                $product = Mage::registry('current_product');
                $sku = $product->getSku();

                if($product->getFinalPrice()) {
                    $price =  $product->getFinalPrice();
                } else if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    $optionCol= $product->getTypeInstance(true)
                        ->getOptionsCollection($product);
                    $selectionCol= $product->getTypeInstance(true)
                        ->getSelectionsCollection(
                            $product->getTypeInstance(true)->getOptionsIds($product),
                            $product
                        );
                    $optionCol->appendSelections($selectionCol);
                    $price = $product->getPrice();

                    foreach ($optionCol as $option) {
                        if($option->required) {
                            $selections = $option->getSelections();
                            $minPrice = min(array_map(function ($s) {
                                return $s->price;
                            }, $selections));
                            if($product->getSpecialPrice() > 0) {
                                $minPrice *= $product->getSpecialPrice()/100;
                            }

                            $price += round($minPrice,2);
                        }
                    }
                } else {
                    $price = "";
                }


                $title = $product->getName();
                $image = $product->getImageUrl();

                if(!is_null(Mage::registry('current_category'))){
                    $iCurrentCategory = Mage::registry('current_category')->getName();
                } else {
                    $iCurrentCategory = '';
                }

                $categoryPath = $product->getCategory();

                $categoriesArr = array();
                if(!is_null($categoryPath)){
                    $categories = explode('/', $categoryPath->getPath());

                    foreach($categories as $catId){
                        $cat = Mage::getModel('catalog/category')->load($catId);
                        if($cat->getLevel()>=2){
                            $categoriesArr[] = $cat->getName();
                        }
                    }

                } else {
                    $categories = $product->getCategoryIds();
                    if(isset($categories[0])){
                        $catId = $categories[0];
                        $cat = Mage::getModel('catalog/category')->load($catId) ;

                        while($cat->getLevel()>=2){
                            $categoriesArr[] = $cat->getName();
                            $cat = $cat->getParentCategory() ;
                        }
                    }

                }



                $segCategories = implode("','",$categoriesArr);

                echo "
                <script>
                Segmentify('view:product', {
                    productId: '".$sku."',
                    categories: ['".$segCategories."'],
                    price: ".(float)$price.",
                    title: '".addslashes($title)."',
                    image: '".$image."',
                    url: '".$currentUrl."',
                    referrer: '".$_SERVER['HTTP_REFERER']."'
                });
                </script>
                ";

                echo "
                <script>
                Segmentify('view:page', {
                    pageUrl: '".$currentUrl."',
                    category: 'Product Page',
                    subCategory: '".$iCurrentCategory."',
                    referrer: '".$_SERVER['HTTP_REFERER']."'
                });
                </script>
                ";
                break;
            case 'result':
                echo "
                <script>
                Segmentify('view:page', {
                    pageUrl: '".$currentUrl."',
                    category: 'Search Page',
                    referrer: '".$_SERVER['HTTP_REFERER']."'
                });
                </script>
                ";
                break;
            default:
                echo "
                <script>
                Segmentify('view:page', {
                    pageUrl: '".$currentUrl."',
                    referrer: '".$_SERVER['HTTP_REFERER']."',
                });
                </script>
                ";
                break;
        }

    }
} 