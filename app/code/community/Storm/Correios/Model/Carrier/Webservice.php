<?php

/**
 * @category   Storm
 * @package    Storm_Correios
 * @copyright  Copyright (c) 2013 Willian Cordeiro de Souza
 * @author     Willian Cordeiro de Souza <williancordeirodesouza@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Storm_Correios_Model_Carrier_Webservice
{
    const WSDL_URL = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx?wsdl';
    protected $_client;
    protected $_params;
    protected $_request;
    protected $_allowedErrors = array('009','010','011');

    /**
     * Performs connection with webservice of Correios
     */
    public function __construct()
    {
        $this->_client = new SoapClient(self::WSDL_URL, array(
            'trace' => true,
            'exceptions' => true,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'connection_timeout' => ini_get('max_execution_timeout'),
            'user_agent' => '',
            'stream_context' => stream_context_create(
                array('http' => array('protocol_version' => '1.0'))
            )
        ));

        if (!$this->_getHelper()->getConfigData('shipping_methods')) {
            throw new Exception($this->_getHelper()->__('You must to select some shipping method.'));
        }

        $this->setParam('nCdEmpresa', $this->_getHelper()->getConfigData('account_code'))
            ->setParam('sDsSenha', $this->_getHelper()->getConfigData('account_password'))
            ->setParam('nCdServico', $this->_getHelper()->getConfigData('shipping_methods'))
            ->setParam('sCepOrigem', Mage::getStoreConfig('shipping/origin/postcode'));
    }

    /**
     * Gets the instance of the SoapClient
     * connected to the webservice
     *
     * @return SoapClient
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Assign parameters to be sent to
     * webservice on request
     *
     * @param string $name
     * @param string $value
     * @return Storm_Correios_Model_Carrier_Webservice
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
        return $this;
    }

    /**
     * Gets all the parameters already setted
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Gets a value only a specific key
     *
     * @param string $key
     * @return boolean | string
     */
    public function getParam($key)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }

        return false;
    }

    /**
     * Assign the request of the delivery method
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Storm_Correios_Model_Carrier_Webservice
     */
    public function setShippingRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $dimension = Mage::getModel('correios/carrier_package_dimension');
        $dimension->setRequest($request);

        $this->setParam('sCepDestino', $request->getDestPostcode())
            ->setParam('nVlPeso', $request->getPackageWeight())
            ->setParam('nCdFormato', 1)
            ->setParam('nVlComprimento', $dimension->getLength())
            ->setParam('nVlAltura', $dimension->getHeight())
            ->setParam('nVlLargura', $dimension->getWidth())
            ->setParam('nVlDiametro', 0)
            ->setParam('sCdMaoPropria', $this->_getHelper()->getConfigData('own_hands') ? 'S' : 'N')
            ->setParam('nVlValorDeclarado', 0)
            ->setParam('sCdAvisoRecebimento', $this->_getHelper()->getConfigData('receipt_warning') ? 'S' : 'N');

        if ($this->_getHelper()->getConfigData('stated_value')) {
            $this->setParam('nVlValorDeclarado', $request->getPackageValue());
        }

        $this->_request = $request;
        return $this;
    }

    /**
     * Gets the request of the delivery method
     *
     * @return Mage_Shipping_Model_Rate_Request
     */
    public function getShippingRequest()
    {
        return $this->_request;
    }

    /**
     * Performs a request with webservice of Correios
     *
     * @return array
     */
    public function request()
    {
        if (!$request = $this->getClient()->CalcPrecoPrazo($this->getParams())) {
            return false;
        }

        if (!$request = $request->CalcPrecoPrazoResult) {
            return false;
        }

        if (!$request = $request->Servicos) {
            return false;
        }

        if (!$request = $request->cServico) {
            return false;
        }

        $result = array();

        if (is_array($request)) {
            foreach ($request as $methodData) {
                $result[] = $this->_convertWebserviceValues($methodData);
            }
        } elseif (isset($request->Codigo)) {
            $result[] = $this->_convertWebserviceValues($request);
        } else {
            throw new Exception(Mage::helper('correios')->__('Cannot be possible to estimate shipping from Correios.'));
        }

        return $result;
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

    /**
     * Converts the data returned from webservice to the object
     * Varien_Object using the parameter names in English
     *
     * @param stdClass $data
     * @return Varien_Object
     */
    private function _convertWebserviceValues(stdClass $data)
    {
        $result = new Varien_Object();
        $result->setCode($data->Codigo);

        $canShowMessage = $this->_isAllowedError($data->Erro);
        if($data->Erro) {
            $result->setError($data->Erro)
                ->setErrorMessage($data->MsgErro)
                ->setShowMessage($canShowMessage);
        }
        
        $result->setPrice($this->_getHelper()->convertToFloat($data->Valor))
            ->setDeliveryTime($data->PrazoEntrega + intval($this->_getHelper()->getConfigData('add_delivery_time')))
            ->setHomeDelivery($data->EntregaDomiciliar == 'S' ? true : false)
            ->setSaturdayDelivery($data->EntregaSabado == 'S' ? true : false);

        return $result;
    }

    /**
     * Conforme o manual os erros de código 009/010/011, são avisos, não pode recusar o calculo devido a este erro,
     * pois são lugares com Área de Risco para entrega, mas o pacote será enviado e cobrado normalmente, somente o
     * prazo que vai comprometer.
     *
     * Página 15 - Item 3. Códigos e mensagens de erro
     * Manual: http://www.correios.com.br/para-voce/correios-de-a-a-z/pdf/calculador-remoto-de-precos-e-prazos/manual-de-implementacao-do-calculo-remoto-de-precos-e-prazos
     *
     * @param string $code
     * @return bool
     */
    protected function _isAllowedError($code)
    {
        if(empty($code) || !in_array($code, $this->_allowedErrors)) {
            return false;
        }

        return true;
    }
}