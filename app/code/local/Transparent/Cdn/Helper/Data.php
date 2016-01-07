<?php

class Transparent_Cdn_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Returns the company id defined on system->config->transparent cdn->configuration
	 * @return string
	 */
	public function getCompanyId()
	{
		return Mage::getStoreConfig('transparent_management/general/company');
	}
	
	/**
	 * Returns the client key defined on system->config->transparent cdn->configuration
	 * @return string
	 */
	public function getUserKey()
	{
		return Mage::getStoreConfig('transparent_management/general/user');
	}

	/**
	 * Returns the client secret key defined on system->config->transparent cdn->configuration
	 * @return string
	 */
	public function getPasswordKey()
	{
		return Mage::getStoreConfig('transparent_management/general/password');
	}
}   

?>