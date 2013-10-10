<?php
/**
 * @category   Storm
 * @package    Storm_Correios
 * @copyright  Copyright (c) 2013 Willian Cordeiro de Souza
 * @author     Willian Cordeiro de Souza <williancordeirodesouza@gmail.com> 
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Storm_Correios_Model_Source_FreeMethods
{    
    /**
     * Gets the methods that can be chosen
     * for free delivery
     *
     * @return array
     */
    public function toOptionArray()
    {
	$methods = Mage::getSingleton('correios/source_methods')->toOptionArray();	
	$others = array(
	    array(
		'label' => Mage::helper('correios')->__('Others'),
		'value' => array(
		    array('value' => 'no-free-shipping', 'label' => Mage::helper('correios')->__('Free shipping does not apply')),
		    array('value' => 'lower-price', 'label' => Mage::helper('correios')->__('Choose by lowest price'))
		)
	    )
	);
	
	return array_merge($others, $methods);
    }
}