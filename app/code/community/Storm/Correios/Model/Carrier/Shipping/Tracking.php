<?php
/**
 * @method setTrackingValue()
 * @method string getTrackingValue()
 */
class Storm_Correios_Model_Carrier_Shipping_Tracking extends Varien_Object
{
    const TRACKING_URL = 'http://webservice.correios.com.br/service/rastro/Rastro.wsdl';
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

			$object = $this->_requestXML($this->getTrackingValue());
			
			foreach($object->evento as $event) {						
				$eventdate = implode("-",array_reverse(explode("/", $event->data)));

				$data[] = array(
					'activity' => $this->_checkEvent($event),
					'deliverydate' => $eventdate,
					'deliverytime' => sprintf('%s:00', $event->hora),
					'deliverylocation' => $event->cidade .' / ' . $event->uf
				);
			}

			return $data;
			
        } catch(Exception $error) {
			Mage::log("Error: " . $error->getMessage());
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
	
	
	
	public function _requestXML($tracking)
    {
		
        $params = array(
            'usuario'   => 'ECT',
            'senha'     => 'SRO',
            'tipo'      => 'L',
            'resultado' => 'T',
            'lingua'    => '101',
            'objetos'   => $tracking
        );
        
        try {
            $client = new SoapClient(self::TRACKING_URL);
            $response = $client->buscaEventos($params);
            if (empty($response)) {
                throw new Exception("Empty response");
            }
						
			return $response->return->objeto;
			
        } catch (Exception $e) {
            Mage::log("Soap Error: {$e->getMessage()}");
            return false;
        }
		return;
    }
	
    public function _checkEvent($event)
    {
        $msg = $event->descricao;
        if (isset($event->destino) && isset($event->destino->local)) {
            $msg .= ' para ' . $event->destino->local . ' - ' . $event->destino->cidade . ' / ' . $event->destino->uf;
        }
        if (isset($event->detalhe) && !empty($event->detalhe)) {
            $msg .= ' ' . $event->detalhe;
        }
        return $msg;
    }

}
