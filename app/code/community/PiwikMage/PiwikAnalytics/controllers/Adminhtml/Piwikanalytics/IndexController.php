<?php
/**
 *
 * Piwik Extension for Magento created by Adrian Speyer
 * Get Piwik at http://www.piwik.org - Open source web analytics
 *
 * @category    Mage
 * @package     Mage_PiwikAnalytics_Controller_IndexController
 * @copyright   Copyright (c) 2012 Adrian Speyer. (http://www.adrianspeyer.com)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

class PiwikMage_PiwikAnalytics_Adminhtml_Piwikanalytics_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {

     $this->loadLayout();
		
		$active = Mage::getStoreConfig(PiwikMage_PiwikAnalytics_Helper_Data::XML_PATH_ACTIVE);
		$siteId = Mage::getStoreConfig(PiwikMage_PiwikAnalytics_Helper_Data::XML_PATH_SITE);
		$installPath = Mage::getStoreConfig(PiwikMage_PiwikAnalytics_Helper_Data::XML_PATH_INSTALL);
		$pwtoken= Mage::getStoreConfig(PiwikMage_PiwikAnalytics_Helper_Data::XML_PATH_PWTOKEN);

      if (!empty($pwtoken)){
	  $block = $this->getLayout()->createBlock('core/text', 'piwik-block')->setText('<iframe src="'.$installPath.'/index.php?module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index&idSite='.$siteId.'&period=week&date=yesterday&token_auth='.$pwtoken.'" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="1000px"></iframe>');
       $this->_addContent($block);
	   $this->_setActiveMenu('piwik_menu')->renderLayout();
	   }
	   
	  	   
	if (empty($pwtoken)){ 
	  $block = $this->getLayout()->createBlock('core/text', 'piwik-block')->setText('You are missing your Piwik Token Auth Key. Get it from your API tab in your Piwik Install.');
       $this->_addContent($block);
	   $this->_setActiveMenu('piwik_menu')->renderLayout();
	   }
	
    }
}
