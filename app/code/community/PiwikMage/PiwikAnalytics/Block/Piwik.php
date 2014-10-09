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
                $productId = $item->product_id;
                $product = Mage::getModel('catalog/product')->load($productId);
                $categoryName = '';
                $categoryIds = $product->getCategoryIds();
                if (!empty($categoryIds)) {
                    $categoryId = $categoryIds[0];
                    $category = Mage::getModel('catalog/category')->load($categoryId);
                    $categoryName = $category->getName();
                }


                if ($item->getQtyOrdered()) {
                    $qty = number_format($item->getQtyOrdered(), 0, '.', '');
                } else {
                    $qty = '0';
                }
                $result[] = sprintf("_paq.push(['addEcommerceItem', '%s', '%s', '%s', %s, %s]);",
                    $this->jsQuoteEscape($item->getSku()),
                    $this->jsQuoteEscape($item->getName()),
                    $categoryName,
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
                $result[] = sprintf("_paq.push(['trackEcommerceOrder' , '%s', %s, %s, %s, %s]);",
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

        foreach ($cart as $cartItem) {

            //get category name
            $productId = $cartItem->product_id;
            $product = Mage::getModel('catalog/product')->load($productId);
            $categoryName = '';
            $categoryIds = $product->getCategoryIds();
            if (!empty($categoryIds)) {
                $categoryId = $categoryIds[0];
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $categoryName = $category->getName();
            }
            $productName = $cartItem->getName();
            $productName = str_replace('"', "", $productName);

            if ($cartItem->getPrice() == 0 || $cartItem->getPrice() < 0.00001):
                continue;
            endif;

            echo "_paq.push(['addEcommerceItem', " . json_encode($cartItem->getSku()) . ", " . json_encode($productName) . ", " . json_encode($categoryName) . ", " . $cartItem->getPrice() . ", " . $cartItem->getQty() . "]);";
            echo "\n";
        }

        //total in cart
        $grandTotal = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
        if ($grandTotal != 0) {
            echo "_paq.push(['trackEcommerceCartUpdate', " . $grandTotal . "]);";
            echo "\n";
        }
    }

    /**
     * Render information when product page view
     * http://piwik.org/docs/ecommerce-analytics/
     */
    protected function _getProductPageview()
    {

        $currentProduct = Mage::registry('current_product');

        if (!($currentProduct instanceof Mage_Catalog_Model_Product)) {
            return;
        }


        $productId = $currentProduct->getId();
        $product = Mage::getModel('catalog/product')->load($productId);
        $categoryName = '';
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $categoryId = $categoryIds[0];
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $categoryName = $category->getName();
        }
        $productName = $currentProduct->getName();

        echo "_paq.push(['setEcommerceView', " . json_encode($currentProduct->getSku()) . ", " . json_encode($productName) . ", " . json_encode($categoryName) . ", " . $currentProduct->getPrice() . " ]);";
        Mage::unregister('current_category');
    }

    /**
     * Render information of category view
     * http://piwik.org/docs/ecommerce-analytics/
     */
    protected function _getCategoryPageview()
    {
        $currentCategory = Mage::registry('current_category');

        if (!($currentCategory instanceof Mage_Catalog_Model_Category)) {
            return;
        }
        echo "_paq.push(['setEcommerceView', false, false, " . json_encode($currentCategory->getName()) . "]);";
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
