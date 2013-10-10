<?php
/**
 * @method setTrackingValue()
 * @method string getTrackingValue()
 */
class Storm_Correios_Model_Carrier_Shipping_Tracking extends Varien_Object
{
    const TRACKING_URL = 'http://websro.correios.com.br/sro_bin/txect01$.QueryList';    
    protected $_result;
    
    public function __construct()
    {
        if(!isset($this->_result)) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }
    }   
    
    /**
     * Makes the request on the website of post office
     * and returns the result of tracking
     * 
     * @return Mage_Shipping_Tracking_Result
     */
    public function request()
    {
        if($trackingData = $this->getTrackingData()) {
            $status = Mage::getModel('shipping/tracking_result_status');
            $status->setCarrier(Storm_Correios_Model_Carrier_Shipping::CODE)
                   ->setCarrierTitle($this->_getHelper()->getConfigData('title'))
                   ->setTracking($this->getTrackingValue())
                   ->addData($trackingData);
            
            $this->_result->append($status);
        } else {
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier(Storm_Correios_Model_Carrier_Shipping::CODE);
            $error->setCarrierTitle($this->_getHelper()->getConfigData('title'));
            $error->setTracking($this->getTrackingValue());
            $error->setErrorMessage($this->_getHelper()->__('Unable to retrieve tracking'));
            
            $this->_result->append($error);
        }
        
        return $this->_result;
    }
    
    /**
     * Returns the formatted information
     * to the result of tracking magento
     * 
     * @return array|boolean
     */
    public function getTrackingData()
    {
        if($progress = $this->_parseProgressDetail()) {
            $data = array(
                'progressdetail' => $progress
            );
            
            return $data;
        }     
        
        return false;
    }
    
    /**
     * Parses the HTML page
     * tracker objects postal
     * 
     * @return array|boolean
     */
    protected function _parseProgressDetail()
    {
        try {
            $data = array();
            $client = new Zend_Http_Client(self::TRACKING_URL);
            $client->setParameterGet('P_LINGUA', '001')
                   ->setParameterGet('P_TIPO', '001')
                   ->setParameterGet('P_COD_UNI', $this->getTrackingValue());

            $response = $client->request(Zend_Http_Client::GET);
            if(preg_match_all('/<tr.*?>(.*?)<\/tr>/i', $response, $rows)) {
                foreach($rows[1] as $row) {
                    preg_match_all('/<td.*?>(.*?)<\/td>/i', $row, $cols);

                    if(count($cols[1]) > 1) {
                        list($deliverydate, $deliverytime) = explode(' ', $cols[1][0]);           
                        $deliverydate = new Zend_Date($deliverydate, 'dd/MM/YYYY', new Zend_Locale('pt_BR'));

                        $data[] = array(
                            'activity' => strip_tags($cols[1][2]),
                            'deliverydate' => $deliverydate->toString('YYYY-MM-dd'),
                            'deliverytime' => sprintf('%s:00', $deliverytime),
                            'deliverylocation' => $cols[1][1]
                        );
                    }
                }

                return $data;
            }
        } catch(Exception $error) {
            //@TODO Gravar log de erro
        }
        
        return false;
    }
    
    /**
     * Returns the instance of the helper module's main
     * 
     * @return Storm_Correios_Helper_Data
     */
    protected function _getHelper()            
    {
        return Mage::helper('correios');
    }
}