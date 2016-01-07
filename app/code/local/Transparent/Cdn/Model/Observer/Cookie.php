<?php

class Transparent_Cdn_Model_Observer_Cookie
{	
	/**
     * Cookie name
     */
	const CACHE_COOKIE_NAME = 'transparent_cookie';
	/**
     * Hash algorithm to use in various cryptographic methods
     */
    const HASH_ALGORITHM    = 'sha256';

    /**
     * Creates a cookie to not cache the urls through Varnish
     */
	public function create_cache_cookie(Varien_Event_Observer $observer)
	{
		if(!$this->cache_cookie_exist())
		{
			if (Mage::getStoreConfig('persistent/options/enabled')) 
			{
				Mage::getModel('core/cookie')->set($this->get_cache_cookie_name(),$this->get_secure_hash(), 60*60);	
			}
			else
			{
				Mage::getModel('core/cookie')->set($this->get_cache_cookie_name(),$this->get_secure_hash());		
			}			
		}
	}

	public function delete_cache_cookie()
	{
		Mage::getModel('core/cookie')->delete($this->get_cache_cookie_name());
	}

	/**
	 * Returns string with the cookie name
	 * @return string
	 */
	private function get_cache_cookie_name()
	{
		return self::CACHE_COOKIE_NAME;
	}

	/**
	 * Returns true if the cache cookie exist, false otherwise
	 * @return bool
	 */
	private function cache_cookie_exist()
	{
		$cacheCookieValue = Mage::getModel('core/cookie')->get( $this->get_cache_cookie_name() );
		return $cacheCookieValue === $this->get_secure_hash();
	}

	/**
     * Get SHA256 hash of a string, salted with encryption key
     * @return string
     */
    private function get_secure_hash() 
    {
    	$data = "transparentCdn";
        $salt = $this->get_crypt_key();
        return hash( self::HASH_ALGORITHM, sprintf('%s:%s', $salt, $data ));
    }

     /**
     * Get Magento's encryption key
     * @return string
     */
    private function get_crypt_key() {
        return (string)Mage::getConfig()->getNode('global/crypt/key');
    }
}

?>