<?php

/**
 * @category   Storm
 * @package    Storm_Correios
 * @copyright  Copyright (c) 2013 Willian Cordeiro de Souza
 * @author     Willian Cordeiro de Souza <williancordeirodesouza@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Storm_Correios_Model_Carrier_Shipping extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    const CODE = 'correios';
    const PACKAGE_WEIGHT_MAX = 30;

    protected $_code = self::CODE;
    protected $_clientRequest;
    protected $_dimension;

    /**
     * It does the calculation and returns shipping prices and terms (if enabled)
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return boolean
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');

        try {
            if (!$this->_getHelper()->isEnabled()) {
                return false;
            }

            if (!$this->isValid($request)) {
                return false;
            }

            if (!$rates = $this->_doRequest($request)) {
                return false;
            }

            foreach ($rates as $rate) {
                if ($rate->hasError() && !$rate->getShowMessage()) {
                    if ($this->_getHelper()->getConfigData('showmethod')) {
                        $this->_appendError($result, sprintf('%s: %s', $this->_getMethodTitle($rate), $rate->getErrorMessage()));
                    }
                    continue;
                }

                $method = Mage::getModel('shipping/rate_result_method');
                $method->setCarrier($this->_code)
                    ->setCarrierTitle($this->getConfigData('title'))
                    ->setMethod($rate->getCode())
                    ->setMethodTitle($this->_getMethodTitle($rate, $this->_canShowDeliverytime()))
                    ->setCost($rate->getPrice())
                    ->setPrice($this->_getFinalPrice($rate, $request));

                $result->append($method);
            }
        } catch (Exception $e) {
            $this->_appendError($result, $e->getMessage());
            return $result;
        }

        return $result;
    }

    /**
     * Check if can show delivery time to customer
     *
     * @return bool
     */
    protected function _canShowDeliverytime()
    {
        return (bool)$this->_getHelper()->getConfigData('show_deliverytime');
    }

    /**
     * Adds the error message in the result
     *
     * @param Mage_Shipping_Model_Rate_Result $result
     * @param string $message
     * @return Storm_Correios_Model_Carrier_Shipping
     */
    protected function _appendError(Mage_Shipping_Model_Rate_Result &$result, $message)
    {
        $this->_getHelper()->log($message);

        $error = Mage::getModel('shipping/rate_result_error');
        $error->setCarrier($this->_code)
            ->setCarrierTitle($this->getConfigData('title'))
            ->setErrorMessage($message);

        $result->append($error);
        return $this;
    }

    /**
     * Assign data received request by webservice
     *
     * @param array $request
     * @return Storm_Correios_Model_Carrier_Shipping
     */
    public function setRequest(array $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Gets the data from the webservice request
     *
     * @return array
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Checks if all details are correct
     * to perform the calculation of shipping
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return boolean
     */
    public function isValid(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!extension_loaded('soap')) {
            throw new Mage_Shipping_Exception($this->_getHelper()->__('You must to install PHP Soap extension to use shipping method Correios.'));
            return false;
        }

        if (!$this->_getHelper()->isValidPostcode($request->getDestPostcode())) {
            throw new Mage_Shipping_Exception($this->_getHelper()->__('Please, enter the postcode correctly.'));
            return false;
        }

        if ($request->getPackageWeight() > self::PACKAGE_WEIGHT_MAX) {
            throw new Mage_Shipping_Exception($this->_getHelper()->__('The package weight exceeds the weight limit.'));
            return false;
        }

        return true;
    }

    /**
     * Gets the delivery methods allowed
     * This method is implemented by the interface
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            $this->_code => $this->getConfigData('name')
        );
    }

    /**
     * Returns all methods of delivery of the module
     * with their respective titles
     *
     * @return array
     */
    public function getShippingMethods()
    {
        $configs = explode(',', $this->getConfigData('methods'));
        $methods = array();
        foreach ($configs as $code) {
            if (!$title = $this->_getHelper()->getMethodTitle($code)) {
                continue;
            }

            $methods[$code] = $title;
        }

        return $methods;
    }

    /**
     * Gets the delivery method with the lowest price
     *
     * @param Varien_Object $rates
     * @return Varien_Data
     */
    public function getLowerPriceMethod(Varien_Object $rates)
    {
        $bestMethod = null;
        foreach ($rates as $rate) {
            if ($rate->hasError() || $rate->getPrice() <= 0) {
                continue;
            }

            if (is_null($bestMethod)) {
                $bestMethod = $rate;
                continue;
            }

            if ($bestMethod->getPrice() > $rate->getPrice()) {
                $bestMethod = $rate;
            }
        }

        return $bestMethod;
    }

    /**
     * Reports that the shipping tracking to is available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Returns information from the post office tracker
     *
     * @param string $tracking
     * @return Mage_Shipping_Model_Tracking_Result|boolean
     */
    public function getTrackingInfo($tracking)
    {
        $model = Mage::getModel('correios/carrier_shipping_tracking');
        $model->setTrackingValue($tracking);

        $result = $model->request();
        if ($result instanceof Mage_Shipping_Model_Tracking_Result) {
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Makes a request to the webservice of Correios
     * Returns the values ​​in array form
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return boolean|array
     */
    protected function _doRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        /* @var $client Storm_Correios_Model_Carrier_Webservice */
        $client = Mage::getModel('correios/carrier_webservice');
        $client->setShippingRequest($request);

        if (!$requestData = $client->request()) {
            return false;
        }

        $this->_setClientRequest($requestData);
        return $requestData;
    }

    /**
     * Assign the result of the request made ​​from webservice
     *
     * @param Varien_Object $request
     * @return Storm_Correios_Model_Carrier_Shipping
     */
    protected function _setClientRequest(array $request)
    {
        $this->_clientRequest = $request;
        return $this;
    }

    /**
     * Gets the result of the request of the webservice
     *
     * @return Varien_Object
     */
    protected function _getClientRequest()
    {
        return $this->_clientRequest;
    }

    /**
     * Gets the title of the delivery method
     * with or without delivery
     *
     * @param Varien_Object $method
     * @param bool $includeDeliveryTime
     * @return string
     */
    protected function _getMethodTitle(Varien_Object $method, $includeDeliveryTime = false)
    {
        $title = $this->_getHelper()->getMethodTitle($method->getCode());

        if($method->getShowMessage() && $method->hasErrorMessage()) {
            $title .= sprintf(' - %s', $method->getErrorMessage());
        }
        
        if ($includeDeliveryTime) {
            if ($method->getDeliveryTime() > 1) {
                return $this->_getHelper()->__('%s (%d working days)', $title, $method->getDeliveryTime());
            } else {
                return $this->_getHelper()->__('%s (%d working day)', $title, $method->getDeliveryTime());
            }
        }

        return $title;
    }

    /**
     * Gets the final price of the freight
     * Follows the rules of free shipping
     *
     * @param Varien_Object $method
     * @return boolean|int|float
     */
    protected function _getFinalPrice(Varien_Object $method, Mage_Shipping_Model_Rate_Request $request)
    {
        $freeMethod = $this->getConfigData('free_shipping_method');

        if (($method->hasError() && !$method->getShowMessage()) || $method->getPrice() <= 0) {
            return false;
        }

        if ($request->getFreeShipping() === true) {
            if ($freeMethod == 'lower-price') {
                if ($bestMethod = $this->getLowerPriceMethod($this->_getClientRequest())) {
                    if ($bestMethod->getCode() == $method->getCode()) {
                        return 0;
                    }
                }
            }

            if ($freeMethod == $method->getCode()) {
                return 0;
            }
        }

        $finalPrice = $method->getPrice();

        if ($handlingFee = $this->getConfigData('handling_fee')) {
            switch ($this->getConfigData('handling_type')) {
                case 'F':
                    $finalPrice += $handlingFee;
                    break;
                case 'P':
                    $finalPrice = ($handlingFee * $finalPrice / 100) + $finalPrice;
                    break;
            }
        }

        return $finalPrice;
    }

    /**
     * Returns the Helper main module
     *
     * @return Storm_Correios_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('correios');
    }
}