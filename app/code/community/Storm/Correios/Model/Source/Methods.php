<?php
/**
 * @category   Storm
 * @package    Storm_Correios
 * @copyright  Copyright (c) 2013 Willian Cordeiro de Souza
 * @author     Willian Cordeiro de Souza <williancordeirodesouza@gmail.com> 
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Storm_Correios_Model_Source_Methods
{    
    /**
     * Gets all possible options
     * methods of Correios
     *
     * @return array
     */
    public function toOptionArray()
    {
	return array(
	    array(
		'label' => Mage::helper('correios')->__('Without contract'),
		'value' => array(
		    array('value' => '40010', 'label' => Mage::helper('correios')->__('Sedex')),
		    array('value' => '40045', 'label' => Mage::helper('correios')->__('Sedex a cobrar')),
		    array('value' => '40215', 'label' => Mage::helper('correios')->__('Sedex 10')),
		    array('value' => '40290', 'label' => Mage::helper('correios')->__('Sedex hoje')),
		    array('value' => '41106', 'label' => Mage::helper('correios')->__('Pac'))
		)
	    ),
	    array(
		'label' => Mage::helper('correios')->__('With contract'),
		'value' => array(
		    array('value' => '40096', 'label' => Mage::helper('correios')->__('Sedex')),
		    array('value' => '40126', 'label' => Mage::helper('correios')->__('Sedex a cobrar')),		    
		    array('value' => '41068', 'label' => Mage::helper('correios')->__('Pac')),
		    array('value' => '81019', 'label' => Mage::helper('correios')->__('e-Sedex')),
		    array('value' => '81027', 'label' => Mage::helper('correios')->__('e-Sedex prioritário')),
		    array('value' => '81035', 'label' => Mage::helper('correios')->__('e-Sedex express')),
		)
	    )
	);
    }
}