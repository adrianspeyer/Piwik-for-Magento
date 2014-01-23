<?php
/**
 *
 * Piwik Extension for Magento created by Adrian Speyer
 * Get Piwik at http://www.piwik.org - Open source web analytics
 *
 * PiwikAnalytics Page Block
 *
 * @category   PiwikMage
 * @package    PiwikMage_PiwikAnalytics
 * @copyright   Copyright (c) 2012 Adrian Speyer. (http://www.adrianspeyer.com)
 * @license     @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

class PiwikMage_PiwikAnalytics_Block_Piwik extends Mage_Core_Block_Template
{

    /**
     * Get a specific page name (may be customized via layout)
     *
     * @return string|null
     */
    public function getPageName()
    {
        return $this->_getData('page_name');
    }

    /**
     * Render information about specified orders and their items
     * http://piwik.org/docs/ecommerce-analytics/
     */
    protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array();

        foreach ($collection as $order) {
            foreach ($order->getAllVisibleItems() as $item) {

                //get category name
                $product_id = $item->product_id;
                $_product = Mage::getModel('catalog/product')->load($product_id);
                $cats = $_product->getCategoryIds();
                $category_id = $cats[0]; // just grab the first id
                $category = Mage::getModel('catalog/category')->load($category_id);
                $category_name = $category->getName();


                if ($item->getQtyOrdered()) {
                    $qty = number_format($item->getQtyOrdered(), 0, '.', '');
                } else {
                    $qty = '0';
                }
                $result[] = sprintf("piwikTracker.addEcommerceItem( '%s', '%s', '%s', %s, %s);",
                    $this->jsQuoteEscape($item->getSku()),
                    $this->jsQuoteEscape($item->getName()),
                    $category_name,
                    $item->getBasePrice(),
                    $qty
                );

            }
            foreach ($collection as $order) {
                if ($order->getGrandTotal()) {
                    $subtotal = $order->getGrandTotal() - $order->getShippingAmount() - $order->getShippingTaxAmount();
                } else {
                    $subtotal = '0.00';
                }
                $result[] = sprintf("piwikTracker.trackEcommerceOrder( '%s', %s, %s, %s, %s);",
                    $order->getIncrementId(),
                    $order->getBaseGrandTotal(),
                    $subtotal,
                    $order->getBaseTaxAmount(),
                    $order->getBaseShippingAmount()
                );


            }
        }
        return implode("\n", $result);
    }

    /**
     * Render information when cart updated
     * http://piwik.org/docs/ecommerce-analytics/
     */
    protected function _getEcommerceCartUpdate()
    {

        $cart = Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();

        foreach ($cart as $cartitem) {

            //get category name
            $product_id = $cartitem->product_id;
            $_product = Mage::getModel('catalog/product')->load($product_id);
            $cats = $_product->getCategoryIds();
            if (isset($cats)) {
                $category_id = $cats[0];
            } // just grab the first id
            $category = Mage::getModel('catalog/category')->load($category_id);
            $category_name = $category->getName();
            $nameofproduct = $cartitem->getName();
            $nameofproduct = str_replace('"', "", $nameofproduct);

            if ($cartitem->getPrice() == 0 || $cartitem->getPrice() < 0.00001):
                continue;
            endif;
            echo 'piwikTracker.addEcommerceItem("' . $cartitem->getSku() . '","' . $nameofproduct . '","' . $category_name . '",' . $cartitem->getPrice() . ',' . $cartitem->getQty() . ');';
            echo "\n";
        }

        //total in cart
        $grandTotal = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
        if ($grandTotal == 0) echo ''; else
            echo 'piwikTracker.trackEcommerceCartUpdate(' . $grandTotal . ');';
        echo "\n";
    }

    /**
     * Render information when product page view
     * http://piwik.org/docs/ecommerce-analytics/
     */
    protected function _getProductPageview()
    {

        $currentproduct = Mage::registry('current_product');

        if (!($currentproduct instanceof Mage_Catalog_Model_Product)) {
            return;
        }


        $product_id = $currentproduct->getId();
        $_product = Mage::getModel('catalog/product')->load($product_id);
        $cats = $_product->getCategoryIds();
        $category_id = $cats[0]; // just grab the first id
        //$category_id = if (isset($cats[0]) {$category_id = $cats[0]} else $category_id = null; potential fix when no catgeories
        $category = Mage::getModel('catalog/category')->load($category_id);
        $category_name = $category->getName();
        $product = $currentproduct->getName();
        $product = str_replace('"', "", $product);


        echo 'piwikTracker.setEcommerceView("' . $currentproduct->getSku() . '", "' . $product . '","' . $category_name . '",' . $currentproduct->getPrice() . ');';
        Mage::unregister('current_category');
    }

    /**
     * Render information of category view
     * http://piwik.org/docs/ecommerce-analytics/
     */
    protected function _getCategoryPageview()
    {
        $currentcategory = Mage::registry('current_category');

        if (!($currentcategory instanceof Mage_Catalog_Model_Category)) {
            return;
        }
        echo 'piwikTracker.setEcommerceView(false,false,"' . $currentcategory->getName() . '");';
        Mage::unregister('current_product');
    }

    /**
     * Render Piwik tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('piwikanalytics')->isPiwikAnalyticsAvailable()) {
            return '';
        }

        return parent::_toHtml();
    }
}
