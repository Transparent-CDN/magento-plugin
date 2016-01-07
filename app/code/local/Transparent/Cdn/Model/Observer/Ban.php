<?php

class Transparent_Cdn_Model_Observer_Ban
{
    /**
     * Magento passes a Varien_Event_Observer object as
     * the first parameter of dispatched events.
     * Events:
     *     catalog_product_save_commit_after
     * @param Observer $observer
     */
    public function catalog_product_save_after(Varien_Event_Observer $observer)
    {
        // Retrieve the product being updated from the event observer
        $product = $observer->getEvent()->getProduct();
        $productUrlsToInvalidate = Mage::helper('transparent_cdn/connection')->get_product_urls($product);    
        $response = Mage::helper('transparent_cdn/connection')->send_to_invalidate($productUrlsToInvalidate);
        $result = Mage::helper('transparent_cdn/connection')->check_result_response($response);
        Mage::helper('transparent_cdn/connection')->set_log($result, $productUrlsToInvalidate, "catalog_product_save_after");
    }

    /**
     * Magento passes a Varien_Event_Observer object as
     * the first parameter of dispatched events.
     * Events:
     *     cataloginventory_stock_item_save_after
     * @param Observer $observer
     */
    public function stock_item_save_after(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getItem();
        if((int)$item->getData('qty') != (int)$item->getOrigData('qty')) 
        {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $productUrlsToInvalidate = Mage::helper('transparent_cdn/connection')->get_product_urls($product);
            $response = Mage::helper('transparent_cdn/connection')->send_to_invalidate($productUrlsToInvalidate);
            $result = Mage::helper('transparent_cdn/connection')->check_result_response($response);
            Mage::helper('transparent_cdn/connection')->set_log($result, $productUrlsToInvalidate, "stock_item_save_after");
        }
    }

    /**
     * Magento passes a Varien_Event_Observer object as
     * the first parameter of dispatched events.
     * Events:
     *      after_reindex_process_cataloginventory_stock
     */
    public function stock_item_save_after_reindex(Varien_Event_Observer $observer)
    {
    }

    /**
     * Magento passes a Varien_Event_Observer object as
     * the first parameter of dispatched events.
     * Events:
     *     catalog_category_save_commit_after
     * @param Observer $observer
     */
    public function catalog_category_save_after(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        $categories = array($category->getUrl());
        $response = Mage::helper('transparent_cdn/connection')->send_to_invalidate($categories);
        $result = Mage::helper('transparent_cdn/connection')->check_result_response($response);
        Mage::helper('transparent_cdn/connection')->set_log($result, $categories, "catalog_category_save_after");
    }
}
?>