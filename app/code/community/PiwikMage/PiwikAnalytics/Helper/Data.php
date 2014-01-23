<?php
/**
 *
 * Piwik Extension for Magento created by Adrian Speyer
 * Get Piwik at http://www.piwik.org - Open source web analytics
 *
 * @category    Mage
 * @package     Mage_PiwikAnalytics_Helper_Data
 * @copyright   Copyright (c) 2012 Adrian Speyer. (http://www.adrianspeyer.com)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */



class PiwikMage_PiwikAnalytics_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_ACTIVE  = 'piwik/analytics/active';
    const XML_PATH_SITE =    'piwik/analytics/site';
	const XML_PATH_INSTALL = 'piwik/analytics/install';
	const XML_PATH_PWTOKEN = 'piwik/analytics/pwtoken';
	
	
    /**
     *
     * @param mixed $store
     * @return bool
     */
    public function isPiwikAnalyticsAvailable($store = null)
    {
        $siteId = Mage::getStoreConfig(self::XML_PATH_SITE, $store);
		//$installPath = Mage::getStoreConfig(self::XML_PATH_INSTALL, $store);
        //return $siteId && $installPath && Mage::getStoreConfigFlag(self::XML_PATH_ACTIVE, $store);
		return $siteId && Mage::getStoreConfigFlag(self::XML_PATH_ACTIVE, $store);
	}
}
