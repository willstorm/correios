<?php

/**
 * @method Mage_Shipping_Model_Rate_Request getRequest()
 * @method Storm_Correios_Model_Carrier_Package_Dimension setError(Exception $error)
 * @method Mage_Shipping_Exception getError()
 * @method Storm_Correios_Model_Carrier_Package_Dimension setWidth(float $value)
 * @method float getWidth()
 * @method Storm_Correios_Model_Carrier_Package_Dimension setHeigth(float $value)
 * @method float getHeigth()
 * @method Storm_Correios_Model_Carrier_Package_Dimension setLength(float $value)
 * @method float getLength()
 */
class Storm_Correios_Model_Carrier_Package_Dimension extends Varien_Object
{
    const PACKAGE_MIN_LENGTH = 16;
    const PACKAGE_MAX_LENGTH = 105;
    const PACKAGE_MIN_HEIGHT = 2;
    const PACKAGE_MAX_HEIGHT = 105;
    const PACKAGE_MIN_WIDTH = 11;
    const PACKAGE_MAX_WIDTH = 105;
    const PACKAGE_MAX_SUM = 200;

    /**
     * Assign requisition
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Storm_Correios_Model_Carrier_Package_Dimension
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        foreach ($request->getAllItems() as $item) {
            $cubic = ceil(pow(($item->getProduct()->getPackageWidth() * $item->getProduct()->getPackageHeight() * $item->getProduct()->getPackageLength()) * $item->getQty(), 1/3));

            $this->setHeight($cubic + $this->getHeight())
                ->setWidth($cubic + $this->getWidth())
                ->setLength($cubic + $this->getLength());
        }

        if ($this->getWidth() < self::PACKAGE_MIN_WIDTH) {
            $this->setWidth(self::PACKAGE_MIN_WIDTH);
        }

        if ($this->getHeight() < self::PACKAGE_MIN_HEIGHT) {
            $this->setHeight(self::PACKAGE_MIN_HEIGHT);
        }

        if ($this->getLength() < self::PACKAGE_MIN_LENGTH) {
            $this->setLength(self::PACKAGE_MIN_LENGTH);
        }

        $this->setData('request', $request);
        return $this;
    }

    /**
     * Checks if the dimensions of packages
     * products are valid
     *
     * @return boolean
     * @throws Mage_Shipping_Exception
     */
    public function isValid()
    {
        try {
            if ($this->getWidth() > self::PACKAGE_MAX_WIDTH) {
                throw new Mage_Shipping_Exception($this->_getHelper()->__('The width of the products is greater than %d cm', self::PACKAGE_MAX_WIDTH));
            }

            if ($this->getHeight() > self::PACKAGE_MAX_HEIGHT) {
                throw new Mage_Shipping_Exception($this->_getHelper()->__('The heigth of the products is greater than %d cm', self::PACKAGE_MAX_HEIGHT));
            }

            if ($this->getLength() > self::PACKAGE_MAX_LENGTH) {
                throw new Mage_Shipping_Exception($this->_getHelper()->__('The length of the products is greater than %d cm', self::PACKAGE_MAX_LENGTH));
            }

            if (($this->getWidth() + $this->getHeigth() + $this->getLength()) > self::PACKAGE_MAX_SUM) {
                throw new Mage_Shipping_Exception($this->_getHelper()->__('The dimensions of the products have passed the limit of of %d cm', self::PACKAGE_MAX_SUM));
            }
        } catch (Mage_Shipping_Exception $error) {
            $this->setError($error);
            return false;
        }

        return true;
    }

    /**
     * Gets the helper module's main
     *
     * @return Storm_Correios_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('correios');
    }
}