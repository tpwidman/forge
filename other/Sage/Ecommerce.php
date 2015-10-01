<?php

namespace Sage;

class Ecommerce extends Core
{

    public $response;

 	public $_commerceXml = array(
        'C_ADDRESS' => '',
        'C_CARDNUMBER' => '',
        'C_CITY' => '',
        'C_COUNTRY' => '', 
        'C_CVV' => '',        
        'C_EMAIL' => '',
        'C_EXP' => '',
        'C_FAX' => '',
        'C_NAME' => '',
        'C_SHIP_ADDRESS' => '',
        'C_SHIP_CITY' => '',
        'C_SHIP_COUNTRY' => '',
        'C_SHIP_NAME' => '',
        'C_SHIP_STATE' => '',
        'C_SHIP_ZIP' => '',
        'C_STATE' => '',
        'C_TELEPHONE' => '',
        'C_ZIP' => '',
        'T_AMT' => '0.00',
        'T_APPLICATION_ID' => 'DEMO',
        'T_CODE' => '1',
        'T_CUSTOMER_NUMBER' => '',
        'T_DEVICE_ID' => '',
        'T_ORDERNUM' => '',
        'T_SHIPPING' => '0.00',
        'T_TAX' => '0.00',
        'T_UTI' => ''
        );


 	private $_payload = '<ECommTransactionRequest xmlns:i="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://schemas.datacontract.org/2004/07/wapiGateway.Models">';

	/**
	 * 
	 * 
	 */ 
	public function postTransaction()
    {
    
    	$return = array('success' => 0, 'message' => '', $result => '', $response = '');

        $xml = trim($this->xmlCreate('ECommTransactionRequest', $this->_commerceXml));
    	$xml = str_replace('<ECommTransactionRequest>', $this->_payload, $xml);
        $this->setEndPoint('ecommercetransactions');
    	$this->setAuthenticationHeader($xml);

		$response = $this->curlPOST($this->getServer() . $this->getEndpoint(), $xml, $this->getHeaders());    	


        echo 'RAW<br/>';
        echo htmlentities($response);
        echo '<hr />';

        $result = $this->parseResponse($response);

        $this->response = $result;

        if ($response->Indicator == 'A') { 
            $return['success'] = 1;
            $return['response'] = $response;
            $return['result'] = $result;
        } else {             
            $return['response'] = $response;
            $return['result'] = $result;
        }

        return $return;

    }

    public function getField()
    {
        $field = strtoupper(trim($field));
        return $this->_commerceXml[$field];
    }

    /**
     * set the type of authorization type that will be submitted.
     * 
     * 1 = Sale
     * 2 = Authorization
     * 3 = Force
     */ 
    public function setAuthorizationType($code)
    {
        if (is_numeric($code) && ($code >= 1 && $code <=3)) { 
            $this->_commerceXml['T_CODE'] = $code;
        }
    }

    /**     
     * 
     */
    public function setField($field, $value)
    {

    	$field = strtoupper(trim($field));
    	array_key_exists($field, $this->_commerceXml) ? $this->_commerceXml[$field] = trim($value) : false;


    } 
}