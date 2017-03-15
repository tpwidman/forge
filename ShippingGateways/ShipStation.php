<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * @copyright 2002 Zeekee Interactive
 */
/**
 * A set of classes/methods making it easier to use the ShipStation API, please note 
 * that you are responsible for any charges incurred by using the Google API.  We always ensure
 * our clients have proper licensing
 */  
class ShipStation
{

    private $apikey;
    private $apisecret;
    private $endpoint;
    private $httpMethod;
    private $authenticationToken;
    private $server = 'https://ssapi.shipstation.com';
    private $boxWidth = 0;
    private $boxHeight = 0;
    private $boxDepth = 0;
    private $originZipCode = '';
    private $address;
    private $shipTo = array();
    private $billTo = array();
    private $boxes;
    private $methods = array();
    private $vars = array();
    private $items = array();
    private $errorMessage = '';

    private $orderStatusOptions = array(
        'awaiting_payment' => 'Awaiting Payment',
        'awaiting_shipment' => 'Awaiting Shipment',
        'on_hold' => 'On Hold',
        'cancelled' => 'Cancelled',
        'shipped' => 'Shipped'
        );

    /**
     * @ignore
     */
    function __construct($username = 'xxxxxxxxxxxx', $password = 'xxxxxxxxxx')
    {

        $this->apikey = $username;
        $this->apisecret = $password;    
        $this->buildAuthenticationToken();
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        
    }

    /**
     * @ignore
     */
    public function __get($index)
    {
        return $this->vars[$index];
    }

    /**
     * @ignore
     */
    public function __set($index, $value)
    {
        $this->vars[$index] = $value;
    }


    public function addItem($vars = array())
    {

        $return = array();

        !empty($vars['lineItemKey']) ? $return[$vars['lineItemKey']] = $vars[$vars['lineItemKey']] : false;
        !empty($vars['sku']) ? $return[$vars['sku']] = $vars[$vars['sku']] : false;
        !empty($vars['name']) ? $return[$vars['name']] = $vars[$vars['name']] : false;
        !empty($vars['imageUrl']) ? $return[$vars['imageUrl']] = $vars[$vars['imageUrl']] : false;
        !empty($vars['quantity']) ? $return[$vars['quantity']] = $vars[$vars['quantity']] : false;
        !empty($vars['unitPrice']) ? $return[$vars['unitPrice']] = $vars[$vars['unitPrice']] : false;
        !empty($vars['taxAmount']) ? $return[$vars['taxAmount']] = $vars[$vars['taxAmount']] : false;
        !empty($vars['shippingAmount']) ? $return[$vars['shippingAmount']] = $vars[$vars['shippingAmount']] : false;
        !empty($vars['warehouseLocation']) ? $return[$vars['warehouseLocation']] = $vars[$vars['warehouseLocation']] : false;
        !empty($vars['productId']) ? $return[$vars['productId']] = $vars[$vars['productId']] : false;
        !empty($vars['fulfillmentSku']) ? $return[$vars['fulfillmentSku']] = $vars[$vars['fulfillmentSku']] : false;
        !empty($vars['adjustment']) ? $return[$vars['adjustment']] = $vars[$vars['adjustment']] : false;
        !empty($vars['upc']) ? $return[$vars['upc']] = $vars[$vars['upc']] : false;
        
        !empty($vars['weight']) ? $return[$vars['weight']] = $vars[$vars['weight']] : false;
        !empty($vars['weight_units']) ? $return[$vars['weight_units']] = $vars[$vars['weight_units']] : false;

        !empty($vars['weight']) ? $return[$vars['weight']] = $vars[$vars['weight']] : false;
        !empty($vars['weight_units']) ? $return[$vars['weight_units']] = $vars[$vars['weight_units']] : false;

        return $array;


    }

   
    /**
     * [buildAuthenticationToken description]
     * @return [type] [description]
     */
    private function buildAuthenticationToken()
    {
        $this->authenticationToken = base64_encode($this->apikey . ':' . $this->apisecret);       

    }    

    /**
     * [createOrder description]
     * @return [type] [description]
     */
    public function createOrder() 
    {

        
        $data = array(
            "orderNumber" => $this->orderNumber,
            "orderKey" => $this->orderKey,
            "orderDate" => $this->orderDate,
            "paymentDate" => $this->paymentDate,
            "shipByDate" => $this->shipByDate,
            "orderStatus" => $this->orderStatus,
            "customerId" => $this->customerId,
            "customerUsername" => $this->customerUsername,
            "customerEmail" => $this->customerEmail,
            "billTo" => $this->getAddress('billTo', 'array'),
            "shipTo" => $this->getAddress('shipTo', 'array')
            );

        $ret = $this->curl('/orders/createorder', $data, array(), 'POST');

        if ($ret->orderId > 0) { 
            $this->orderId;
            return true;
        } else { 
            $this->errorMessage = $ret->ExceptionMessage;
            return false;
        }
    }

    /**
     * post data in fields to url
     * 
     * @param  string $cUrl   [URL]
     * @return boolean 
     */
    private function curl($endpoint, $data = '', $headers = array(), $method = 'GET', $debug = 0)
    {        

        $url = $this->server . $endpoint;

        $headers['Authorization'] = 'Basic ' . $this->authenticationToken;        

        $output = array();

        $httpAuth = false;

        $httpAuthValue = false;

        $curlHeaders = array();

        if (sizeof($headers) > 0) {
            foreach($headers as $key => $value) { 
                if (array_key_exists('HTTPAUTH', $headers)) {
                    $httpAuth = true;
                    $httpAuthValue = $headers['HTTPAUTH'];
                } else { 
                    $curlHeaders[] = "$key: $value";
                }
            }                    
        }

        if (is_array($headers)) { 
            if (array_key_exists('Content-Type', $headers) && strpos($headers['Content-Type'], 'xml') > 0 ) {
                $query = $data;
            } else {     
                if (is_object($data) || is_array($data)) {            
                    $query = trim(http_build_query($data, '', '&'));    
                } else { 
                    $query = $data;
                }
            }
        } else { 
            if (is_object($data) || is_array($data)) {            
                $query = trim(http_build_query($data, '', '&'));    
            } else { 
                $query = $data;
            }
        }

        ($method == 'GET' && !empty($query)) ? $url = $url . '?'. $query : false;

        $ch = curl_init();                  // URL of gateway for cURL to post to
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.example.com/'); // to prevent error code 500
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) ? curl_setopt($ch, CURLOPT_CAINFO, 'C:\WINNT\curl-ca-bundle.crt') : false;

        if ($httpAuth) { 
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $httpAuthValue);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders); 
    
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);                
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);            
        }

        if ($debug) { 
            curl_setopt($ch, CURLOPT_HEADER, 1); // set to 0 to eliminate header info from response       
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        } else { 
            curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response       
            curl_setopt($ch, CURLINFO_HEADER_OUT, false);
        }
        
        $response = curl_exec($ch);    
        $output = new StdClass();
        
        if ($debug) { 
            $output = (object) curl_getinfo($ch);
            $output->query = trim($query);
            $output->headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        }
                
        if (!$response) {
            $output->response = curl_error($ch) . '::' . curl_errno($ch); 
        } else { 
            $output->response = $response;    
        }    

        return $output;
    }   

    /**
     * [getError description]
     * @return [type] [description]
     */
    public function getError()
    {
        return $this->errorMessage;
    }

    /**
     * [getAutenticationToken description]
     * @return [type] [description]
     */
    public function getAutenticationToken()
    {
        return $this->authenticationToken;
    }
    
    /**
     * [getCarrier description]
     * @return [type] [description]
     */
    public function getCarrier($carrier = '')
    {
        // error
        return $this->curl('/carriers/getcarrier', 
            array('carrierCode' => $carrier), 
            array(),'GET', 0);

    }

    /**
     * [getCustomer description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getCustomer($id)
    {
        // error
        return $this->curl('/carriers/' . $id,'', array(),'GET', 0);

    }

    /**
     * [getRates description]
     * @return [type]       [description]
     */
    public function getRates()
    {        

        $return = array();

        $carriers = $this->listCarriers();

        foreach ($this->boxes as $n => $box) { 
            foreach ($carriers as $key => $value) {                 
                $return[$value->code] = array();
                $vars = array(
                    'carrier' => $value->code,
                    'fromPostalCode' => $this->shipTo->originZipCode,
                    'toState' => $this->shipTo->toState,
                    'toCountry' => $this->shipTo->toCountry,
                    'toPostalCode' => $this->shipTo->toPostalCode,
                    'toCity' => $this->shipTo->toCity,
                    'weight' => ($box['weight']*16) // conversion to ounces
                    );

                $rates = json_decode($this->getRate($vars)->response);

                foreach ($rates as $r => $rate) { 
                    $return[$value->code][$rate->serviceName] = array(
                        'MAILSERVICE' => $rate->serviceName,
                        'RATE' => $rate->shipmentCost,
                        'ADDRESS' => $this->getAddress()
                        );
                }                
            }
        }

        if (sizeof($carriers) == 0) { 
            $return['ERROR']['ERROR'] = array(
                'MAILSERVICE' => 'ERROR',
                'RATE' => '',
                'ADDRESS' => $this->getAddress()
            );
        }

        return $return;

    }

    /**
     * [getAddress description]
     * @return [type] [description]
     */
    public function getAddress($type = 'shipTo', $return = 'string') 
    {
        if($return == 'array') { 
            if (strtolower($type) == 'shipto') { 
                return array(
                    "name" => $this->shipTo->toName,
                    "company" => $this->shipTo->toCompany,
                    "street1" => $this->shipTo->toStreet1,
                    "street2" => $this->shipTo->toStreet2,
                    "street3" => $this->shipTo->toStreet3,
                    "city" => $this->shipTo->toCity,
                    "state" => $this->shipTo->toState,
                    "postalCode" => $this->shipTo->toPostalCode,
                    "country" => $this->shipTo->toCountry,
                    "phone" => $this->shipTo->toPhone,
                    "residential" => $this->shipTo->toResidential
                );
            } else { 
                return array(
                    "name" => $this->billTo->toName,
                    "company" => $this->billTo->toCompany,
                    "street1" => $this->billTo->toStreet1,
                    "street2" => $this->billTo->toStreet2,
                    "street3" => $this->billTo->toStreet3,
                    "city" => $this->billTo->toCity,
                    "state" => $this->billTo->toState,
                    "postalCode" => $this->billTo->toPostalCode,
                    "country" => $this->billTo->toCountry,
                    "phone" => $this->billTo->toPhone,
                    "residential" => $this->billTo->toResidential
                );
            }        
        } else { 
            if (strtolower($type) == 'shipto') { 
                return $this->shipTo->toStreet1 . ',' 
                . $this->shipTo->toCity . ',' 
                . $this->shipTo->toState . ',' 
                . $this->shipTo->toPostalCode . ',' 
                . $this->shipTo->toCountry;        
            } else { 
                return $this->billTo->toStreet1 . ',' 
                . $this->billTo->toCity . ',' 
                . $this->billTo->toState . ',' 
                . $this->billTo->toPostalCode . ',' 
                . $this->billTo->toCountry;        
            }
        }
        
    }

    /**
     * [getRate description]
     * @param  [type] $vars [description]
     * @return [type]       [description]
     */
    private function getRate($vars)
    {
        
        /*
        "dimensions" => array(
                'length' => 7, 
                'width' => 6, 
                'height' => 5, 
                'units' => 'inches'),  
         */

        $query = array(
            "carrierCode" => $vars['carrier'],
            "serviceCode" => null,
            "packageCode" => null,
            "fromPostalCode" => $vars['fromPostalCode'],
            "toState" => $vars['toState'],
            "toCountry" => $vars['toCountry'],
            "toPostalCode" => $vars['toPostalCode'],
            "toCity" => $vars['toCity'],
            "weight" => array('value' => $vars['weight'], 'units' => 'ounces'),            
            "confirmation" => "delivery",
            "residential" => 'false'
            );
        return $this->curl('/shipments/getrates', $query, array(), 'POST');

    }

    /**
     * [listCarriers description]
     * @return [type] [description]
     */
    public function listCarriers()
    {
        return json_decode($this->curl('/carriers','',array())->response);

    }

    /**
     * [listCustomers description]
     * @return [type] [description]
     */
    public function listCustomers($vars = array())
    {
        return $this->curl('/customers', $vars, array());

    }

    /**
     * [listCustomers description]
     * @return [type] [description]
     */
    public function listFulfillments($vars = array())
    {
        return $this->curl('/fulfillments', $vars, array());

    }

     /**
     * [listPackages description]
     * @param  string $carrier [description]
     * @return [type]          [description]
     */
    public function listPackages($carrier = '')
    {
        // error
        return $this->curl('/carriers/listpackages', 
            array('carrierCode' => $carrier), 
            array(),'GET', 0);

    }

    /**
     * [listServices description]
     * @param  string $carrier [description]
     * @return [type]          [description]
     */
    public function listServices($carrier = '')
    {
        // error
        return $this->curl('/carriers/listservices', 
            array('carrierCode' => $carrier), 
            array(),'GET', 0);

    }

    /**
     * [listTags description]
     * @return [type] [description]
     */
    public function listTags()
    {
        return $this->curl('/accounts/listtags','',array());

    }

    /**
     * [listTags description]
     * @return [type] [description]
     */
    public function listUsers($showInactive = false)
    {

        //showInactive=showInactive

        return $this->curl('/users','',array());

    }





    /**
     * [setServer description]
     */
    public function setServer()
    {

    }

    /**
     * [setAddress description]
     */
    public function setAddress($vars = array(), $type = 'shipTo')
    {

        foreach ($vars as $key => $value) { 
            if (!preg_match("/^to/is", $key)) { 
                (strtolower($key) == 'name') ? $vars['toName'] = $value : false;
                (strtolower($key) == 'company') ? $vars['toCompany'] = $value : false;
                (strtolower($key) == 'street1') ? $vars['toStreet1'] = $value : false;
                (strtolower($key) == 'street2') ? $vars['toStreet2'] = $value : false;
                (strtolower($key) == 'street3') ? $vars['toStreet3'] = $value : false;
                (strtolower($key) == 'city') ? $vars['toCity'] = $value : false;
                (strtolower($key) == 'state') ? $vars['toState'] = $value : false;
                (strtolower($key) == 'postalcode') ? $vars['toPostalCode'] = $value : false;
                (strtolower($key) == 'country') ? $vars['toCountry'] = $value : false;
                unset($vars[$key]);
            }
        }

        (strtolower($type) == 'billto') ? $this->billTo = (object) $vars : $this->shipTo = (object) $vars;    
    }

    /**
     * [setBoxes description]
     * @param [type] $boxes [description]
     */
    public function setBoxes($boxes) { 
        $this->boxes = $boxes;
    }

    /**
     * [set the handling fee associated with all requests]
     */
    public function setDefaultBox($height, $width, $depth) 
    {
        $this->boxDepth = $depth;
        $this->boxWidth = $width;
        $this->boxHeight - $height;
    }

    /**
     * [set the handling fee associated with all requests]
     */
    public function setHandlingFee($fee) 
    {
        $this->uspsHanldingFee = $fee;
    }

    /**
     * [set the handling fee associated with all requests]
     */
    public function setOriginZipCode($code) 
    {
        $this->originZipCode = $code;
    }

}
