<?php
/**
 * @category   Storm
 * @package    Storm_Correios
 * @copyright  Copyright (c) 2013 Willian Cordeiro de Souza
 * @author     Willian Cordeiro de Souza <williancordeirodesouza@gmail.com> 
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Storm_Correios_Model_Adminhtml_Observer 
{    
    /**
     * Check if there are errors in the module configuration
     * and requirements for the delivery method
     * work properly
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    public function checkConfiguration(Varien_Event_Observer $observer) 
    {
        try {
            if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
                return false;
            }

            if(!Mage::helper('adminnotification')->isModuleEnabled()) {
                return false;
            }
            
            if(!extension_loaded('soap')) {
                throw new Exception(Mage::helper('correios')->__('The extension of PHP Soap must be installed so that the module Correios to function properly.'));
            }

            if (Mage::getStoreConfig('shipping/origin/country_id') != 'BR') {
                throw new Exception(Mage::helper('correios')->__('The Correios module accepts deliveries only national, so the home country of delivery must be configured to Brazil. Do not forget to set the postcode too.'));
            }

            return true;
        } catch(Exception $e) {            
            $this->_addMessage($e->getMessage());
        }
    }
    
    protected function _addMessage($message)
    {
        $this->_getInbox()->getResource()->parse($this->_getInbox(), array(
            array(
                'severity' => Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL,
                'title' => $message,
                'date_added' => date('Y-m-d H:i:s'),
                'url' => '',
            )
        ));
        
        return $this;
    }
    
    /**
     * Gets the message model
     * 
     * @return Mage_AdminNotification_Model_Inbox
     */
    protected function _getInbox()
    {
	return Mage::getSingleton('adminnotification/inbox');
    }

}