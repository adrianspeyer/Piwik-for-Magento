<?php
/**
 *
 * Piwik Extension for Magento created by Adrian Speyer
 * Get Piwik at http://www.piwik.org - Open source web analytics
 *
 * @category    PiwikMage
 * @package     PiwikMage_PiwikAnalytics
 * @copyright   Copyright (c) 2012 Adrian Speyer. (http://www.adrianspeyer.com)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */


/**
 * Piwik module observer
 *
 * @category   PiwikMage
 * @package    PiwikMage_PiwikAnalytics
 */
class PiwikMage_PiwikAnalytics_Model_Observer
{
   

    /**
     * Add order information into Piwik block to render on checkout success pages
     *
     * @param Varien_Event_Observer $observer
     */
    public function setPiwikAnalyticsOnOrderSuccessPageView(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $block = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('piwik_analytics');
        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }


   
}
   
   