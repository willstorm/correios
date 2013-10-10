<?php
/**
 * @category   Storm
 * @package    Storm_Correios
 * @copyright  Copyright (c) 2013 Willian Cordeiro de Souza
 * @author     Willian Cordeiro de Souza <williancordeirodesouza@gmail.com> 
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$attributeGroup = 'Dimensions';

$attributes = array(
    'package_height' => array(
	'group'			     => $attributeGroup,
	'input'			     => 'text',
	'type'			     => 'decimal',
	'label'			     => 'Package Height (cm)',
	'visible'		     => true,
	'required'		     => false,
	'user_defined'		     => false,
	'searchable'		     => false,
	'filterable'		     => false,
	'comparable'		     => false,
	'visible_on_front'	     => false,
	'visible_in_advanced_search' => false,
	'is_html_allowed_on_front'   => false,
	'used_for_promo_rules'	     => true,
	'frontend_class'	     => 'validate-number',	
	'global'		     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'apply_to'		     => 'simple,grouped,configurable'
    ),
    'package_width' => array(
	'group'			     => $attributeGroup,
	'input'			     => 'text',
	'type'			     => 'decimal',
	'label'			     => 'Package Width (cm)',
	'visible'		     => true,
	'required'		     => false,
	'user_defined'		     => false,
	'searchable'		     => false,
	'filterable'		     => false,
	'comparable'		     => false,
	'visible_on_front'	     => false,
	'visible_in_advanced_search' => false,
	'is_html_allowed_on_front'   => false,
	'used_for_promo_rules'	     => true,
	'frontend_class'	     => 'validate-number',	
	'global'		     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'apply_to'		     => 'simple,grouped,configurable'
    ),
    'package_length' => array(
	'group'			     => $attributeGroup,
	'input'			     => 'text',
	'type'			     => 'decimal',
	'label'			     => 'Package Length (cm)',
	'visible'		     => true,
	'required'		     => false,
	'user_defined'		     => false,
	'searchable'		     => false,
	'filterable'		     => false,
	'comparable'		     => false,
	'visible_on_front'	     => false,
	'visible_in_advanced_search' => false,
	'is_html_allowed_on_front'   => false,
	'used_for_promo_rules'	     => true,
	'frontend_class'	     => 'validate-number',	
	'global'		     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'apply_to'		     => 'simple,grouped,configurable'
    )
);


/* @var $installer Storm_Correios_Model_Catalog_Resource_Setup */
$installer = $this;
$installer->startSetup();

foreach($installer->getAllAttributeSets(Mage_Catalog_Model_Product::ENTITY) as $attributeSet) {
    $installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, $attributeSet['attribute_set_name'], $attributeGroup, 1000);
}

foreach($attributes as $attributeCode => $options) {
    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode, $options);
}

$translate = Mage::getResourceModel('core/translate_string');
$translate->saveTranslate($attributeGroup, 'Dimensões', 'pt_BR', 0)
	  ->saveTranslate('Package Width (cm)', 'Largura do pacote (cm)', 'pt_BR', 0)
	  ->saveTranslate('Package Height (cm)', 'Altura do pacote (cm)', 'pt_BR', 0)
	  ->saveTranslate('Package Length (cm)', 'Comprimento do pacote (cm)', 'pt_BR', 0);

$installer->endSetup();