<?php
/**
 * @category   Storm
 * @package    Storm_Correios
 * @copyright  Copyright (c) 2013 Willian Cordeiro de Souza
 * @author     Willian Cordeiro de Souza <williancordeirodesouza@gmail.com> 
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Storm_Correios_Helper_Data extends Mage_Core_Helper_Abstract
{
    const LOG_FILENAME = 'correios.log';
    
    /**
     * Checks whether the delivery method of Correios
     * and the module is enabled
     * 
     * @return bool
     */
    public function isEnabled()
    {
        if(!$this->isModuleEnabled()) {
            return false;
        }
        
        return $this->getConfigData('active') ? true : false;
    }
    
    /**
     * Gets module configuration
     * 
     * @param string|null $key
     * @return mixed
     */
    public function getConfigData($key = null)
    {
        $path = 'carriers/' . Storm_Correios_Model_Carrier_Shipping::CODE;
        
        if(!is_null($key)) {
            $path .= '/' . $key;
        }
        
        return Mage::getStoreConfig($path);
    }    
    
    /**
     * Returns the title of the method of the supply Methods
     * 
     * @param string $code
     * @return string|boolean
     */
    public function getMethodTitle($code)
    {
	$options = Mage::getSingleton('correios/source_methods')->toOptionArray();       
        
	foreach($options as $option) {
	    if(is_array($option['value'])) {
		foreach($option['value'] as $method) {
		    if($method['value'] == $code) {
			return $method['label'];
		    }
		}
	    } else {
		if($option['value'] == $code) {
		    return $option['label'];
		}
	    }
	}
	
	return false;
    }
    
    /**
     * Converts a string in the format
     * decimal to float
     * 
     * @param string $value
     * @return float
     */
    public function convertToFloat($value)
    {        
        return floatval(str_replace(array('.',','), array(null,'.'), $value));
    }
    
    /**
     * Removes formatting zip
     * 
     * @param string $postcode
     * @return string
     */
    public function removePostcodeFormat($postcode)
    {
        return str_replace('-', null, trim($postcode));
    }
    
    /**
     * Checks whether a given zip code is valid
     * 
     * @param type $postcode
     * @return boolean
     */
    public function isValidPostcode($postcode)
    {
        if(!$postcode = $this->removePostcodeFormat($postcode)) {
            return false;
        }
        
        if(!preg_match('/^[0-9]{8}$/i', $postcode)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Writes a log message
     * Checks whether the mode is enabled Developer Log
     * 
     * @param string $message
     * @param string $level
     * @return boolean
     */
    public function log($message, $code = null, $level = null)
    {        
        if(!$this->getConfigData('debug_mode')) {
            return false;
        }
        
        if(!is_null($code) && !empty($code)) {
            $message = sprintf('%s: %s', $code, $message);
        }
        
        Mage::log($message, $level, self::LOG_FILENAME);
        return true;
    }
}