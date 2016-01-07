<?php

class Transparent_Cdn_Helper_Connection extends Mage_Core_Helper_Abstract {

	/**
     * Returns true if the desired url goes to invalidate false otherwise
     * @param json response
     * @return bool
     */
    public function check_result_response($response)
    {
        $sendCorrectly = false;

        if(isset($response->locked_urls[0]))
        {
            $sendCorrectly = false;
        }
        
        if(isset($response->urls_to_send[0])) 
        {
            $sendCorrectly = true;
        }

        return $sendCorrectly;
    }

    /**
     * Performs a curl request and send to invalidate the product url and returns the response.
     * @param array urlsToInvalidate
     * @return json
     */
    public function send_to_invalidate($urlsToInvalidate) 
    {
        $companyId = Mage::helper('transparent_cdn/data')->getCompanyId();
        $token = $this->get_token();
        $url = 'https://api.transparentcdn.com/v1/companies/'.$companyId.'/invalidate/';
        $aCurl = $this->get_curl_headers($token);
        $post = '{"urls":[';
        for($i = 0; $i < count($urlsToInvalidate); $i++)
        {
            if($i == 0)
            {
                $post .= '"'.$urlsToInvalidate[$i].'"';
            }
            else
            {
                $post .= ',"'.$urlsToInvalidate[$i].'"';
            }
        }
        $post .= ']}';

        $response = json_decode($this->get_response($url,$post,$aCurl));
        return $response;
    }

    /**
     * Returns the token to comunicate with transparent cdn, empty string if something goes wrong
     * @return string
     */
    public function get_token()
    {
        $token = "";
        $client_id = Mage::helper('transparent_cdn/data')->getUserKey();
        $client_secret = Mage::helper('transparent_cdn/data')->getPasswordKey();

        $tokenUrl = 'https://api.transparentcdn.com/v1/oauth2/access_token/';
        $post = 'client_id='.$client_id.'&client_secret='.$client_secret.'&grant_type=client_credentials';

        $response = json_decode($this->get_response($tokenUrl,$post));

        if(isset($response->access_token))
        {
            $token = $response->access_token;
        } 

        return $token;
    }

    /**
     * Returns an array with the curloptions to perform the request.
     * @param string token
     * @return array
     */
    public function get_curl_headers($token)
    {
        return  array(
                    CURLOPT_HTTPHEADER  => array(
                                            'Authorization: Bearer '.$token,
                                            'Content-Type: application/json',
                                            ),
                );
    }

    /**
     * Returns the desired response from the provided url
     * @param string url
     * @param string post
     * @param array curlParameters
     * @param bool false
     * @return string
     */
    public function get_response($url, $post = '', $curlParameters = '', $raw = FALSE) {        
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6000);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        //curl_setopt($ch, CURLOPT_USERAGENT, 'trovitbot');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if ($curlParameters) {
            foreach ($curlParameters as $key => $value) {
                curl_setopt($ch, $key, $value);
            }
        }

        $html = curl_exec($ch);

        curl_close($ch);
        
        return $html;
    }

    /**
     * Get the regex for banning a product page from the cache, including
     * any parent products for configurable/group products
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function get_product_urls($product) {
        $urlPatterns = array();
        foreach( $this->get_parent_products( $product ) as $parentProduct ) {
            if ( $parentProduct->getProductUrl() ) {
                $urlPatterns[] = $parentProduct->getProductUrl();
            }
        }
        if ( $product->getProductUrl() ) {
            $urlPatterns[] = $product->getProductUrl();
        }
        return $urlPatterns;
    }

    /**
     * Get parent products of a configurable or group product
     *
     * @param  Mage_Catalog_Model_Product $childProduct
     * @return array
     */
    public function get_parent_products($childProduct) {
        $parentProducts = array();
        foreach( array( 'configurable', 'grouped' ) as $pType ) {
            foreach( Mage::getModel( 'catalog/product_type_' . $pType )
                    ->getParentIdsByChild( $childProduct->getId() ) as $parentId ) {
                $parentProducts[] = Mage::getModel( 'catalog/product' )
                    ->load( $parentId );
            }
        }
        return $parentProducts;
    }

    /**
     * Set the log with the result of the request
     * @param bool $result
     * @param product product
     */
    public function set_log($result, $productUrlsToInvalidate, $method)
    {
        $message = "";
        if($result)
        {
            $message = "urls for function ".$method." send to invalidate, urls:";
            foreach($productUrlsToInvalidate as $url)
            {
                $message .= " ".$url.",";
            }
            
        }
        else
        {
            $message = "urls for function ".$method." locked. Transparent can not clean the following urls:";
            foreach($productUrlsToInvalidate as $url)
            {
                $message .= " ".$url.",";
            }
        }

        Mage::log(
            $message,
            null,
            'product-updates.log'
        );
    }
	
}   

?>